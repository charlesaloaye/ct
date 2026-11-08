<?php

namespace App\Support;

use App\Models\Setting;
use Throwable;

/**
 * Applies the mail and AI settings saved in Admin → Integrations over the
 * .env values. Anything left empty in the admin keeps the .env value, so a
 * server can be configured either way.
 */
final class RuntimeConfig
{
    public static function apply(): void
    {
        try {
            Setting::forgetLoaded();
            $settings = SiteSettings::all();
        } catch (Throwable) {
            // No database yet (fresh install, before migrations): keep .env values.
            return;
        }

        self::mail($settings['mail']);
        self::ai($settings['ai']);
    }

    private static function mail(array $mail): void
    {
        $set = array_filter([
            'site.contact_recipient' => $mail['contactRecipient'],
            'mail.from.address' => $mail['fromAddress'],
            'mail.from.name' => $mail['fromName'],
        ], 'filled');

        if (filled($mail['mailer'])) {
            $set['mail.default'] = $mail['mailer'];
        }

        if ($mail['mailer'] === 'smtp') {
            $set += [
                'mail.mailers.smtp.host' => $mail['host'],
                'mail.mailers.smtp.port' => (int) $mail['port'],
                'mail.mailers.smtp.username' => $mail['username'],
                'mail.mailers.smtp.password' => SiteSettings::secret('mail', 'password'),
                // "ssl" = implicit TLS (port 465); "tls" and "none" = plain smtp, upgraded with STARTTLS when offered.
                'mail.mailers.smtp.scheme' => $mail['encryption'] === 'ssl' ? 'smtps' : 'smtp',
            ];
        }

        if ($set === []) {
            return;
        }

        config($set);

        // Mailers are cached once built; rebuild them with the new settings.
        if (app()->resolved('mail.manager')) {
            app('mail.manager')->purge('smtp');
            app('mail.manager')->purge(config('mail.default'));
        }
    }

    private static function ai(array $ai): void
    {
        $set = array_filter([
            'ai.provider' => $ai['provider'],
            'ai.max_tokens' => $ai['maxTokens'] ? (int) $ai['maxTokens'] : null,
            'ai.voice' => $ai['voice'],
            'ai.openai.model' => $ai['openaiModel'],
            'ai.openai.api_key' => SiteSettings::secret('ai', 'openaiKey'),
            'ai.anthropic.model' => $ai['anthropicModel'],
            'ai.anthropic.api_key' => SiteSettings::secret('ai', 'anthropicKey'),
        ], 'filled');

        if ($set !== []) {
            config($set);
        }
    }
}
