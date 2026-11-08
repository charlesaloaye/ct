<?php

use App\Services\Ai\AiWriter;
use App\Services\Ai\AnthropicWriter;
use App\Services\Ai\OpenAiWriter;
use App\Services\Ai\Prompts;
use App\Services\Ai\TextBlocks;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

beforeEach(fn () => actingAsAdmin());

function fakeWriter(): AiWriter
{
    $fake = new class () implements AiWriter {
        public array $calls = [];

        public function draft(string $title, string $prompt, string $tone, string $length): array
        {
            $this->calls[] = ['draft', $title, $prompt, $tone, $length];

            return TextBlocks::parse("Intro about {$title}.\n\n## The problem\n\nIt was slow.");
        }

        public function rewrite(string $text, string $action, string $context = ''): string
        {
            $this->calls[] = ['rewrite', $text, $action];

            return "[{$action}] {$text}";
        }

        public function seo(string $title, string $excerpt, string $body): array
        {
            $this->calls[] = ['seo', $title, $excerpt, $body];

            return ['seoTitle' => 'Short title', 'seoDescription' => 'Short description'];
        }

        public function ping(): string
        {
            return 'fake-model';
        }
    };
    app()->instance(AiWriter::class, $fake);

    return $fake;
}

/** A Responses API reply with the given text (and a reasoning item first, as reasoning models send). */
function openAiReply(string $text, array $extra = []): array
{
    return $extra + [
        'id' => 'resp_1', 'object' => 'response', 'status' => 'completed', 'model' => 'gpt-test-2026',
        'output' => [
            ['type' => 'reasoning', 'id' => 'rs_1', 'summary' => []],
            ['type' => 'message', 'id' => 'msg_1', 'role' => 'assistant', 'content' => [['type' => 'output_text', 'text' => $text, 'annotations' => []]]],
        ],
        'usage' => ['input_tokens' => 120, 'output_tokens' => 80, 'output_tokens_details' => ['reasoning_tokens' => 30], 'input_tokens_details' => ['cached_tokens' => 0]],
    ];
}

function useOpenAi(): void
{
    config(['ai.provider' => 'openai', 'ai.openai.api_key' => 'sk-test', 'ai.openai.model' => 'gpt-test-2026']);
}

it('drafts an article as body blocks', function () {
    $fake = fakeWriter();

    $this->postJson('/admin/ai/draft', ['title' => 'Paystack vs Flutterwave', 'prompt' => 'Compare fees', 'tone' => 'Practical', 'length' => 'Short (~700 words)'])
        ->assertOk()
        ->assertJsonPath('data.body.1', ['type' => 'h2', 'text' => 'The problem', 'id' => 'the-problem']);
    expect($fake->calls[0])->toBe(['draft', 'Paystack vs Flutterwave', 'Compare fees', 'Practical', 'Short (~700 words)']);
});

it('rewrites a paragraph', function (string $action) {
    fakeWriter();
    $this->postJson('/admin/ai/rewrite', ['text' => 'Old text.', 'action' => $action])->assertOk()->assertJsonPath('data.text', "[{$action}] Old text.");
})->with(['continue', 'clearer', 'example', 'shorten']);

it('generates SEO fields', function () {
    fakeWriter();
    $this->postJson('/admin/ai/seo', ['title' => 'T', 'excerpt' => 'E', 'body' => 'B'])->assertOk()->assertJsonPath('data.seoTitle', 'Short title');
});

it('validates AI requests', function () {
    fakeWriter();
    $this->postJson('/admin/ai/draft', ['title' => 'T'])->assertUnprocessable()->assertJsonValidationErrors('prompt');
    $this->postJson('/admin/ai/rewrite', ['text' => 'x', 'action' => 'delete-everything'])->assertJsonValidationErrors('action');
});

it('calls the OpenAI Responses API with the voice, model and token limit, and parses the draft', function () {
    useOpenAi();
    config(['ai.max_tokens' => 9000, 'ai.voice' => 'Write like a calm engineer.']);
    Http::fake(['api.openai.com/*' => Http::response(openAiReply("Opening line — with a dash.\n\n## Fees\n\nThey differ."))]);
    Log::spy();

    $this->postJson('/admin/ai/draft', ['title' => 'Fees', 'prompt' => 'Compare fees'])
        ->assertOk()
        ->assertJsonPath('data.body.0.text', 'Opening line - with a dash.')
        ->assertJsonPath('data.body.1.type', 'h2');

    Http::assertSent(fn (Request $r) => $r->url() === 'https://api.openai.com/v1/responses'
        && $r->hasHeader('Authorization', 'Bearer sk-test')
        && $r['model'] === 'gpt-test-2026'
        && $r['max_output_tokens'] === 9000
        && $r['store'] === false
        && str_starts_with($r['instructions'], 'Write like a calm engineer.')
        && str_contains($r['input'], 'Compare fees'));
    Log::shouldHaveReceived('info')->withArgs(fn ($msg, $ctx) => $msg === 'AI usage' && $ctx['provider'] === 'openai' && $ctx['output_tokens'] === 80);
});

it('asks OpenAI for structured JSON for SEO and trims to the limits', function () {
    useOpenAi();
    Http::fake(['api.openai.com/*' => Http::response(openAiReply(json_encode([
        'seoTitle' => str_repeat('Long title words ', 10),
        'seoDescription' => 'A clear description.',
    ])))]);

    $this->postJson('/admin/ai/seo', ['title' => 'T', 'body' => 'B'])->assertOk()
        ->assertJsonPath('data.seoDescription', 'A clear description.');
    expect(mb_strlen($this->postJson('/admin/ai/seo', ['title' => 'T', 'body' => 'B'])->json('data.seoTitle')))->toBeLessThanOrEqual(60);

    Http::assertSent(fn (Request $r) => $r['text']['format']['type'] === 'json_schema' && $r['text']['format']['strict'] === true);
});

it('turns OpenAI failures into plain messages', function (int $status, array $error, string $message, int $expect) {
    useOpenAi();
    Http::fake(['api.openai.com/*' => Http::response(['error' => $error], $status)]);

    $this->postJson('/admin/ai/rewrite', ['text' => 'x', 'action' => 'clearer'])->assertStatus($expect)->assertJsonPath('message', $message);
})->with([
    [401, ['code' => 'invalid_api_key', 'message' => 'bad'], 'The OpenAI API key was rejected. Check it in Admin → Integrations.', 503],
    [404, ['code' => 'model_not_found', 'message' => 'nope'], 'The model "gpt-test-2026" was not found or this key cannot use it. Check the model name in Admin → Integrations.', 503],
    [429, ['code' => 'insufficient_quota', 'message' => 'quota'], 'The OpenAI account has no credit left. Add billing at platform.openai.com, then try again.', 503],
    [429, ['code' => 'rate_limit_exceeded', 'message' => 'slow'], 'The AI provider is rate limiting requests. Wait a minute and try again.', 429],
]);

it('reports refusals and cut-off structured output', function () {
    useOpenAi();
    Http::fake(['api.openai.com/*' => Http::sequence()
        ->push(['status' => 'completed', 'model' => 'm', 'output' => [['type' => 'message', 'content' => [['type' => 'refusal', 'refusal' => 'No']]]]])
        ->push(openAiReply('{"seoTitle": "cut', ['status' => 'incomplete', 'incomplete_details' => ['reason' => 'max_output_tokens']]))]);

    $this->postJson('/admin/ai/rewrite', ['text' => 'x', 'action' => 'clearer'])->assertStatus(422)->assertJsonPath('message', 'The AI declined this request. Try rewording the brief.');
    $this->postJson('/admin/ai/seo', ['title' => 'T', 'body' => 'B'])->assertStatus(502)->assertJsonPath('message', 'The AI ran out of room before finishing. Raise the token limit in Admin → Integrations.');
});

it('explains what is missing when no key or model is set', function (string $provider, array $config, string $message) {
    config(['ai.provider' => $provider] + $config);
    Http::fake();

    $this->postJson('/admin/ai/rewrite', ['text' => 'x', 'action' => 'clearer'])->assertStatus(503)->assertJsonPath('message', $message);
    Http::assertNothingSent();
})->with([
    ['openai', ['ai.openai.api_key' => null, 'ai.openai.model' => 'm'], 'The AI assistant is not set up: add the OpenAI API key in Admin → Integrations.'],
    ['openai', ['ai.openai.api_key' => 'k', 'ai.openai.model' => null], 'The AI assistant is not set up: add the OpenAI model in Admin → Integrations.'],
    ['anthropic', ['ai.anthropic.api_key' => null, 'ai.anthropic.model' => 'm'], 'The AI assistant is not set up: add the Anthropic API key in Admin → Integrations.'],
    ['anthropic', ['ai.anthropic.api_key' => 'k', 'ai.anthropic.model' => null], 'The AI assistant is not set up: add the Anthropic model in Admin → Integrations.'],
]);

it('picks the writer for the chosen provider', function () {
    config(['ai.provider' => 'openai']);
    expect(app(AiWriter::class))->toBeInstanceOf(OpenAiWriter::class);
    config(['ai.provider' => 'anthropic']);
    expect(app(AiWriter::class))->toBeInstanceOf(AnthropicWriter::class);
});

it('uses the built-in voice until the admin writes one', function () {
    config(['ai.voice' => null]);
    expect(Prompts::voice())->toBe(Prompts::VOICE);
    config(['ai.voice' => 'Mine.']);
    expect(Prompts::voice())->toBe('Mine.');
});

it('rate limits AI to 20 requests an hour', function () {
    fakeWriter();
    foreach (range(1, 20) as $i) {
        $this->postJson('/admin/ai/seo', ['title' => 'T', 'body' => 'B'])->assertOk();
    }
    $this->postJson('/admin/ai/seo', ['title' => 'T', 'body' => 'B'])->assertTooManyRequests();
});
