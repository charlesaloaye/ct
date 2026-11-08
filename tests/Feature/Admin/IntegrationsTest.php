<?php

use App\Models\Setting;
use App\Support\RuntimeConfig;
use App\Support\SiteSettings;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(fn () => actingAsAdmin(['email' => 'owner@example.com']));

$ai = fn (array $extra = []) => $extra + ['provider' => 'openai', 'openaiModel' => 'gpt-test-2026', 'anthropicModel' => null, 'maxTokens' => 12000, 'voice' => null, 'openaiKey' => null, 'anthropicKey' => null];

it('stores the AI key encrypted, applies it, and never sends it back', function () use ($ai) {
    $this->put('/admin/integrations/ai', $ai(['openaiKey' => 'sk-live-secret-9876']))->assertRedirect()->assertSessionHasNoErrors();

    $raw = json_encode(Setting::query()->where('key', 'ai')->sole()->value);
    expect($raw)->not->toContain('sk-live-secret-9876')
        ->and(SiteSettings::secret('ai', 'openaiKey'))->toBe('sk-live-secret-9876');

    RuntimeConfig::apply();
    expect(config('ai.openai.api_key'))->toBe('sk-live-secret-9876')
        ->and(config('ai.openai.model'))->toBe('gpt-test-2026')
        ->and(config('ai.max_tokens'))->toBe(12000);

    $page = $this->get('/admin/integrations');
    $page->assertInertia(fn (Assert $p) => $p
        ->component('admin/Integrations')
        ->where('ai.openaiKey', null)
        ->where('ai.openaiKeySet', true)
        ->where('ai.openaiKeyHint', '•••• 9876'));
    expect($page->getContent())->not->toContain('sk-live-secret');
});

it('keeps the stored key when the field is left empty, and removes it on request', function () use ($ai) {
    $this->put('/admin/integrations/ai', $ai(['openaiKey' => 'sk-one']));
    $this->put('/admin/integrations/ai', $ai(['openaiModel' => 'other-model']));
    expect(SiteSettings::secret('ai', 'openaiKey'))->toBe('sk-one');

    $this->put('/admin/integrations/ai', $ai(['clear' => ['openaiKey']]));
    expect(SiteSettings::secret('ai', 'openaiKey'))->toBeNull();
});

it('validates AI settings', function () use ($ai) {
    $this->put('/admin/integrations/ai', $ai(['provider' => 'skynet']))->assertSessionHasErrors('provider');
    $this->put('/admin/integrations/ai', $ai(['maxTokens' => 10]))->assertSessionHasErrors('maxTokens');
    $this->put('/admin/integrations/ai', $ai(['openaiModel' => 'bad model; drop']))->assertSessionHasErrors('openaiModel');
});

it('tests the AI connection and reports the model or the problem', function () use ($ai) {
    $this->put('/admin/integrations/ai', $ai(['openaiKey' => 'sk-test']));
    Http::fake(['api.openai.com/*' => Http::sequence()
        ->push(['status' => 'completed', 'model' => 'gpt-test-2026', 'output' => [['type' => 'message', 'content' => [['type' => 'output_text', 'text' => 'OK']]]]])
        ->push(['error' => ['code' => 'invalid_api_key']], 401)]);

    $this->post('/admin/integrations/ai/test')->assertRedirect();
    expect(Inertia\Inertia::getFlashed()['toast'])->toBe('AI is working: gpt-test-2026 replied.');

    $this->post('/admin/integrations/ai/test');
    expect(Inertia\Inertia::getFlashed()['toast'])->toContain('key was rejected');
});

it('stores SMTP settings with an encrypted password and applies them to the mailer', function () {
    $this->put('/admin/integrations/mail', [
        'mailer' => 'smtp', 'host' => 'smtp.zoho.com', 'port' => 465, 'username' => 'hello@charlestechy.com', 'password' => 'app-password-1',
        'encryption' => 'ssl', 'fromAddress' => 'hello@charlestechy.com', 'fromName' => 'Charles', 'contactRecipient' => 'inbox@charlestechy.com',
    ])->assertRedirect()->assertSessionHasNoErrors();

    expect(json_encode(Setting::query()->where('key', 'mail')->sole()->value))->not->toContain('app-password-1');

    RuntimeConfig::apply();
    expect(config('mail.default'))->toBe('smtp')
        ->and(config('mail.mailers.smtp.host'))->toBe('smtp.zoho.com')
        ->and(config('mail.mailers.smtp.port'))->toBe(465)
        ->and(config('mail.mailers.smtp.scheme'))->toBe('smtps')
        ->and(config('mail.mailers.smtp.password'))->toBe('app-password-1')
        ->and(config('mail.from.address'))->toBe('hello@charlestechy.com')
        ->and(config('site.contact_recipient'))->toBe('inbox@charlestechy.com');
});

it('requires host, port and sender for SMTP', function () {
    $this->put('/admin/integrations/mail', ['mailer' => 'smtp'])->assertSessionHasErrors(['host', 'port', 'fromAddress']);
    $this->put('/admin/integrations/mail', ['mailer' => 'carrier-pigeon'])->assertSessionHasErrors('mailer');
});

it('sends a test email to the signed-in admin', function () {
    config(['mail.default' => 'smtp']);
    Mail::fake();
    $this->post('/admin/integrations/mail/test')->assertRedirect();

    expect(Inertia\Inertia::getFlashed()['toast'])->toContain('owner@example.com');
});

it('reports a failing mail server in plain words', function () {
    config(['mail.default' => 'smtp', 'mail.mailers.smtp.host' => '127.0.0.1', 'mail.mailers.smtp.port' => 1, 'mail.mailers.smtp.timeout' => 1]);

    $this->post('/admin/integrations/mail/test')->assertRedirect();
    expect(Inertia\Inertia::getFlashed()['toast'])->toStartWith('Sending failed:');
});
