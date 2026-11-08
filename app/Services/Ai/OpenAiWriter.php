<?php

namespace App\Services\Ai;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/** AiWriter backed by the OpenAI Responses API (POST /v1/responses). */
class OpenAiWriter extends PromptedWriter
{
    protected function complete(string $system, string $user, string $purpose, ?array $schema = null): array
    {
        $config = config('ai.openai');
        $key = $config['api_key'] ?? null;
        $model = $config['model'] ?? null;

        if (blank($key)) {
            throw AiException::notConfigured('OpenAI API key');
        }
        if (blank($model)) {
            throw AiException::notConfigured('OpenAI model');
        }

        $body = array_filter([
            'model' => $model,
            'instructions' => $system,
            'input' => $user,
            'max_output_tokens' => (int) config('ai.max_tokens'),
            'store' => false,
            'reasoning' => filled($config['reasoning_effort']) ? ['effort' => $config['reasoning_effort']] : null,
            'text' => $schema ? ['format' => ['type' => 'json_schema', 'name' => $purpose, 'schema' => $schema, 'strict' => true]] : null,
        ], fn ($v) => $v !== null);

        try {
            $response = Http::withToken($key)
                ->withHeaders(array_filter(['OpenAI-Organization' => $config['organization'] ?? null]))
                ->acceptJson()
                ->timeout((int) $config['timeout'])
                ->retry(2, 1000, fn ($e) => $e instanceof ConnectionException
                    || in_array($e->response?->status(), [500, 502, 503, 504], true), throw: false)
                ->post(rtrim($config['base_url'], '/').'/responses', $body);
        } catch (ConnectionException $e) {
            Log::warning('OpenAI connection error', ['purpose' => $purpose, 'message' => $e->getMessage()]);
            throw new AiException('Could not reach the AI provider. Try again in a moment.');
        }

        if ($response->failed()) {
            throw $this->error($response, $model, $purpose);
        }

        $data = $response->json();
        $usage = $data['usage'] ?? [];

        Log::info('AI usage', [
            'provider' => 'openai',
            'purpose' => $purpose,
            'model' => $data['model'] ?? $model,
            'input_tokens' => $usage['input_tokens'] ?? null,
            'output_tokens' => $usage['output_tokens'] ?? null,
            'reasoning_tokens' => $usage['output_tokens_details']['reasoning_tokens'] ?? null,
            'cached_tokens' => $usage['input_tokens_details']['cached_tokens'] ?? null,
            'status' => $data['status'] ?? null,
        ]);

        $text = '';
        foreach ($data['output'] ?? [] as $item) {
            foreach (($item['type'] ?? null) === 'message' ? ($item['content'] ?? []) : [] as $part) {
                if (($part['type'] ?? null) === 'refusal') {
                    throw new AiException('The AI declined this request. Try rewording the brief.', 422);
                }
                if (($part['type'] ?? null) === 'output_text') {
                    $text .= $part['text'];
                }
            }
        }

        if (($data['status'] ?? null) === 'incomplete') {
            $reason = $data['incomplete_details']['reason'] ?? null;
            if ($reason === 'content_filter') {
                throw new AiException('The AI declined this request. Try rewording the brief.', 422);
            }
            if ($schema !== null || $text === '') {
                throw new AiException('The AI ran out of room before finishing. Raise the token limit in Admin → Integrations.');
            }
        }

        return ['text' => $text, 'model' => (string) ($data['model'] ?? $model)];
    }

    private function error(Response $response, string $model, string $purpose): AiException
    {
        $error = $response->json('error') ?? [];
        Log::warning('OpenAI API error', ['purpose' => $purpose, 'status' => $response->status(), 'code' => $error['code'] ?? null, 'message' => $error['message'] ?? null]);

        return match (true) {
            $response->status() === 401 => new AiException('The OpenAI API key was rejected. Check it in Admin → Integrations.', 503),
            $response->status() === 404 || ($error['code'] ?? null) === 'model_not_found' => new AiException("The model \"{$model}\" was not found or this key cannot use it. Check the model name in Admin → Integrations.", 503),
            ($error['code'] ?? null) === 'insufficient_quota' => new AiException('The OpenAI account has no credit left. Add billing at platform.openai.com, then try again.', 503),
            $response->status() === 429 => new AiException('The AI provider is rate limiting requests. Wait a minute and try again.', 429),
            $response->status() === 400 => new AiException('The AI provider rejected the request: '.($error['message'] ?? 'bad request').'.', 502),
            default => new AiException('The AI provider returned an error. Try again in a moment.'),
        };
    }
}
