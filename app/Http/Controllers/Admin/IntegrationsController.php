<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Ai\AiException;
use App\Services\Ai\AiWriter;
use App\Services\Ai\Prompts;
use App\Support\RuntimeConfig;
use App\Support\SiteSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

/**
 * AI writing assistant and outgoing email. Keys and passwords are stored
 * encrypted and never sent back to the browser; empty fields keep the
 * current value (or the .env value when none was saved here).
 */
class IntegrationsController extends Controller
{
    public function edit(): Response
    {
        $ai = SiteSettings::forAdmin('ai');
        $mail = SiteSettings::forAdmin('mail');

        return Inertia::render('admin/Integrations', [
            'ai' => [
                ...$ai,
                'provider' => $ai['provider'] ?: config('ai.provider'),
                'maxTokens' => $ai['maxTokens'] ?: config('ai.max_tokens'),
                'openaiModel' => $ai['openaiModel'] ?: config('ai.openai.model'),
                'anthropicModel' => $ai['anthropicModel'] ?: config('ai.anthropic.model'),
                // Whether a key exists anywhere (admin or .env), so the page can say "using the server key".
                'openaiKeyAvailable' => filled(config('ai.openai.api_key')),
                'anthropicKeyAvailable' => filled(config('ai.anthropic.api_key')),
                'defaultVoice' => Prompts::VOICE,
                'requestsPerHour' => config('ai.requests_per_hour'),
            ],
            'mail' => [
                ...$mail,
                'effectiveMailer' => config('mail.default'),
                'effectiveFrom' => config('mail.from.address'),
                'effectiveRecipient' => config('site.contact_recipient') ?: SiteSettings::value('contact.email'),
            ],
            'adminEmail' => request()->user()->email,
            'meta' => ['title' => 'Integrations · Admin'],
        ]);
    }

    public function updateAi(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            ...SiteSettings::rules('ai'),
            'clear' => ['nullable', 'array'],
            'clear.*' => ['in:openaiKey,anthropicKey'],
        ]);

        SiteSettings::save('ai', Arr::except($validated, 'clear'), $validated['clear'] ?? []);
        RuntimeConfig::apply();

        Inertia::flash('toast', 'AI settings saved');

        return back();
    }

    /** One tiny request to the provider, so a wrong key or model shows up here rather than in the editor. */
    public function testAi(AiWriter $writer): RedirectResponse
    {
        try {
            $model = $writer->ping();
            Inertia::flash('toast', "AI is working: {$model} replied.");
        } catch (AiException $e) {
            Inertia::flash('toast', $e->getMessage());
        }

        return back();
    }

    public function updateMail(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            ...SiteSettings::rules('mail'),
            'clear' => ['nullable', 'array'],
            'clear.*' => ['in:password'],
        ]);

        SiteSettings::save('mail', Arr::except($validated, 'clear'), $validated['clear'] ?? []);
        RuntimeConfig::apply();

        Inertia::flash('toast', 'Email settings saved');

        return back();
    }

    /** Sends a test email right away (not queued) so a wrong password or host shows here. */
    public function testMail(Request $request): RedirectResponse
    {
        $to = $request->user()->email;

        try {
            Mail::raw(
                "This is a test from your website's admin. If you can read it, email is set up correctly.\n\n".config('app.url'),
                fn ($m) => $m->to($to)->subject('Test email from '.parse_url(config('app.url'), PHP_URL_HOST)),
            );
            $note = in_array(config('mail.default'), ['log', 'array'], true)
                ? 'Test written to the log only: choose SMTP to really send email.'
                : "Test email sent to {$to}. Check the inbox (and spam folder).";
            Inertia::flash('toast', $note);
        } catch (Throwable $e) {
            report($e);
            Inertia::flash('toast', 'Sending failed: '.str($e->getMessage())->limit(180));
        }

        return back();
    }
}
