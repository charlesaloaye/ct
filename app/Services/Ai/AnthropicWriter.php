<?php

namespace App\Services\Ai;

use Anthropic\Client;
use Anthropic\Core\Exceptions\APIConnectionException;
use Anthropic\Core\Exceptions\APIStatusException;
use Anthropic\Core\Exceptions\AuthenticationException;
use Anthropic\Core\Exceptions\NotFoundException;
use Anthropic\Core\Exceptions\RateLimitException;
use Illuminate\Support\Facades\Log;

/** AiWriter backed by the Anthropic Messages API (official PHP SDK). */
class AnthropicWriter extends PromptedWriter
{
    protected function complete(string $system, string $user, string $purpose, ?array $schema = null): array
    {
        $config = config('ai.anthropic');
        $key = $config['api_key'] ?? null;
        $model = $config['model'] ?? null;

        if (blank($key)) {
            throw AiException::notConfigured('Anthropic API key');
        }
        if (blank($model)) {
            throw AiException::notConfigured('Anthropic model');
        }

        $client = new Client(apiKey: $key, baseUrl: $config['base_url'] ?: null);
        $format = $schema ? ['type' => 'json_schema', 'schema' => $schema] : null;
        $params = [
            'model' => $model,
            'maxTokens' => (int) config('ai.max_tokens'),
            'system' => $system,
            'messages' => [['role' => 'user', 'content' => $user]],
            'outputConfig' => array_filter(['effort' => $config['effort'] ?: null, 'format' => $format]) ?: null,
            'requestOptions' => ['timeout' => (float) $config['timeout'], 'maxRetries' => 2],
        ];

        try {
            // With fallbacks on, a request the model declines is re-run on a fallback model in the same call.
            $message = filled($config['fallbacks'])
                ? $client->beta->messages->create(...$params, betas: ['server-side-fallback-2026-07-01'], fallbacks: $config['fallbacks'])
                : $client->messages->create(...$params);
        } catch (AuthenticationException) {
            throw new AiException('The Anthropic API key was rejected. Check it in Admin → Integrations.', 503);
        } catch (NotFoundException) {
            throw new AiException("The model \"{$model}\" was not found. Check the model name in Admin → Integrations.", 503);
        } catch (RateLimitException) {
            throw new AiException('The AI provider is rate limiting requests. Wait a minute and try again.', 429);
        } catch (APIStatusException $e) {
            Log::warning('Anthropic API error', ['purpose' => $purpose, 'type' => $e->type?->value, 'message' => $e->getMessage()]);
            throw new AiException('The AI provider returned an error. Try again in a moment.');
        } catch (APIConnectionException $e) {
            Log::warning('Anthropic connection error', ['purpose' => $purpose, 'message' => $e->getMessage()]);
            throw new AiException('Could not reach the AI provider. Try again in a moment.');
        }

        Log::info('AI usage', [
            'provider' => 'anthropic',
            'purpose' => $purpose,
            'model' => $message->model,
            'input_tokens' => $message->usage->inputTokens,
            'output_tokens' => $message->usage->outputTokens,
            'cache_read_input_tokens' => $message->usage->cacheReadInputTokens,
            'stop_reason' => $message->stopReason,
        ]);

        if ($message->stopReason === 'refusal') {
            throw new AiException('The AI declined this request. Try rewording the brief.', 422);
        }

        if ($message->stopReason === 'max_tokens' && $schema !== null) {
            throw new AiException('The AI ran out of room before finishing. Raise the token limit in Admin → Integrations.');
        }

        $text = collect($message->content)->filter(fn ($b) => $b->type === 'text')->map(fn ($b) => $b->text)->join('');

        return ['text' => $text, 'model' => (string) $message->model];
    }
}
