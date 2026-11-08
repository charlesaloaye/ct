<?php

namespace App\Support;

use App\Models\Setting;
use App\Rules\SafeEmail;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\Rule;
use Throwable;

/**
 * The shape of every admin-editable setting: defaults, validation rules and
 * which fields are secrets. Secrets are stored encrypted and never leave the
 * server; the admin only sees whether one is set.
 */
final class SiteSettings
{
    /** Settings groups and the fields each one holds, with empty defaults. */
    public const DEFAULTS = [
        'identity' => [
            'name' => null,          // Full name: footer, author box, emails
            'shortName' => null,     // Header brand name
            'initials' => null,      // Author avatar, admin avatar
            'mark' => null,          // The square logo mark (letters, shown when there is no logo image)
            'logoPath' => null,      // Logo image: replaces the letters in the header, admin and browser tab
            'wordmark' => null,      // Large footer wordmark
            'role' => null,          // Under the header name
            'authorTitle' => null,   // Under the author name on articles
            'location' => null,      // Footer line
            'publicName' => null,    // The name the public and search engines should use (e.g. "Charles Aloaye")
            'alternateNames' => [],  // Other names people know you by (e.g. "CharlesTechy", full name)
            'jobTitles' => [],       // Professional titles, in order of importance
        ],
        'about' => [
            'body' => null,          // The About page biography (paragraphs separated by a blank line)
            'bioShort' => null,      // Standard bios for profiles and press, kept identical everywhere
            'bioMedium' => null,
            'nameNote' => null,      // How to refer to you in print
        ],
        'hero' => [
            'headline' => null,
            'intro' => null,
            'portraitPath' => null,
            'portraitAlt' => null,
            'primaryLabel' => null,
            'primaryUrl' => null,
            'cvLabel' => null,
            'shippedTitle' => null,
            'shippedNote' => null,
            'shippedUrl' => null,
        ],
        'availability' => ['enabled' => true, 'text' => null, 'shortText' => null],
        'contact' => ['email' => null, 'phone' => null, 'whatsappUrl' => null, 'location' => null, 'cvPath' => null, 'cvDownloadName' => null],
        'socials' => [],
        'appearance' => ['theme' => 'light', 'accent' => '#3DDC97'],
        'sections' => [
            'clients' => ['label' => null],
            'work' => ['eyebrow' => null, 'heading' => null, 'linkLabel' => null],
            'workPage' => ['eyebrow' => null, 'title' => null, 'lead' => null],
            'products' => ['eyebrow' => null, 'heading' => null, 'note' => null],
            'rescue' => ['eyebrow' => null, 'heading' => null, 'body' => null, 'cta' => null],
            'services' => ['eyebrow' => null, 'heading' => null, 'lead' => null],
            'process' => ['eyebrow' => null, 'heading' => null],
            'toolkit' => ['eyebrow' => null, 'heading' => null],
            'experience' => ['eyebrow' => null, 'heading' => null],
            'contact' => ['heading' => null, 'lead' => null, 'needs' => [], 'button' => null, 'success' => null, 'altPrompt' => null, 'altLink' => null],
            'blog' => ['eyebrow' => null, 'title' => null, 'lead' => null, 'searchHint' => null, 'newsletterTitle' => null, 'newsletterText' => null],
            'article' => ['ctaTitle' => null, 'ctaText' => null, 'ctaButton' => null],
            'caseStudy' => ['ctaTitle' => null, 'ctaButton' => null],
            'aboutPage' => ['eyebrow' => null, 'title' => null, 'lead' => null],
            'experiencePage' => ['eyebrow' => null, 'title' => null, 'lead' => null],
            'venturesPage' => ['eyebrow' => null, 'title' => null, 'lead' => null],
            'productsPage' => ['eyebrow' => null, 'title' => null, 'lead' => null],
            'expertisePage' => ['eyebrow' => null, 'title' => null, 'lead' => null],
            'mediaPage' => ['eyebrow' => null, 'title' => null, 'lead' => null],
            'contactPage' => ['eyebrow' => null, 'title' => null, 'lead' => null],
        ],
        'seo' => [
            'titleSuffix' => null,
            'shareImagePath' => null,
            'home' => ['title' => null, 'description' => null],
            'work' => ['title' => null, 'description' => null],
            'blog' => ['title' => null, 'description' => null],
            'about' => ['title' => null, 'description' => null],
            'experience' => ['title' => null, 'description' => null],
            'ventures' => ['title' => null, 'description' => null],
            'products' => ['title' => null, 'description' => null],
            'expertise' => ['title' => null, 'description' => null],
            'media' => ['title' => null, 'description' => null],
            'contact' => ['title' => null, 'description' => null],
            // Site verification and analytics. Nothing loads until a value is set.
            'googleVerification' => null,
            'bingVerification' => null,
            'gaMeasurementId' => null,
        ],
        'ai' => [
            'provider' => null,      // openai | anthropic; empty uses AI_PROVIDER
            'openaiModel' => null,
            'anthropicModel' => null,
            'maxTokens' => null,
            'voice' => null,
            'openaiKey' => null,
            'anthropicKey' => null,
        ],
        'mail' => [
            'mailer' => null,        // smtp | log; empty uses .env
            'host' => null,
            'port' => null,
            'username' => null,
            'password' => null,
            'encryption' => null,    // tls | ssl | none
            'fromAddress' => null,
            'fromName' => null,
            'contactRecipient' => null,
        ],
    ];

    /** Pages with their own search title and description. */
    public const SEO_PAGES = ['home', 'work', 'blog', 'about', 'experience', 'ventures', 'products', 'expertise', 'media', 'contact'];

    /** Encrypted fields, per group. */
    public const SECRETS = [
        'ai' => ['openaiKey', 'anthropicKey'],
        'mail' => ['password'],
    ];

    /** Every group merged over its defaults (secrets still encrypted). */
    public static function all(): array
    {
        $stored = Setting::allKeyed();

        return collect(self::DEFAULTS)
            ->map(fn ($default, $key) => self::merge($default, $stored[$key] ?? null))
            ->all();
    }

    public static function group(string $key): array
    {
        return self::all()[$key];
    }

    /** A decrypted secret, or null when unset or unreadable (e.g. after an APP_KEY change). */
    public static function secret(string $group, string $field): ?string
    {
        $value = self::group($group)[$field] ?? null;

        if (blank($value)) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Save a group. Secrets: an empty value keeps the stored one, `clear` removes it.
     *
     * @param  list<string>  $clear  secret fields to remove
     */
    public static function save(string $key, array $value, array $clear = []): void
    {
        if (array_is_list(self::DEFAULTS[$key])) {
            Setting::put($key, array_values($value));

            return;
        }

        $current = self::group($key);
        $next = self::merge($current, $value);

        foreach (self::SECRETS[$key] ?? [] as $field) {
            if (in_array($field, $clear, true)) {
                $next[$field] = null;
            } elseif (filled($value[$field] ?? null)) {
                $next[$field] = Crypt::encryptString($value[$field]);
            } else {
                $next[$field] = $current[$field];
            }
        }

        Setting::put($key, $next);
    }

    /** A group as the admin sees it: secrets replaced by "is it set" flags. */
    public static function forAdmin(string $key): array
    {
        $group = self::group($key);

        foreach (self::SECRETS[$key] ?? [] as $field) {
            $plain = self::secret($key, $field);
            $group[$field] = null;
            $group[$field.'Set'] = $plain !== null;
            $group[$field.'Hint'] = $plain !== null ? '•••• '.mb_substr($plain, -4) : null;
        }

        return $group;
    }

    /** Validation rules for one group, keyed by field. */
    public static function rules(string $key): array
    {
        $text = fn (int $max = 255) => ['nullable', 'string', 'max:'.$max];
        $url = ['nullable', 'url:http,https', 'max:255'];
        $link = ['nullable', 'string', 'max:255', 'regex:/^(https?:\/\/|mailto:|tel:|\/|#)/i'];

        return match ($key) {
            'identity' => [
                'name' => ['required', 'string', 'max:120'],
                'shortName' => ['required', 'string', 'max:60'],
                'initials' => ['required', 'string', 'max:4'],
                'mark' => ['required', 'string', 'max:4'],
                'logoPath' => $text(),
                'wordmark' => ['required', 'string', 'max:40'],
                'role' => $text(80),
                'authorTitle' => $text(120),
                'location' => $text(120),
                'publicName' => ['required', 'string', 'max:80'],
                'alternateNames' => ['nullable', 'array', 'max:6'],
                'alternateNames.*' => ['required', 'string', 'max:80'],
                'jobTitles' => ['nullable', 'array', 'max:6'],
                'jobTitles.*' => ['required', 'string', 'max:60'],
            ],
            'about' => [
                'body' => ['required', 'string', 'max:8000'],
                'bioShort' => $text(300),
                'bioMedium' => $text(1200),
                'nameNote' => $text(300),
            ],
            'hero' => [
                'headline' => ['required', 'string', 'max:160'],
                'intro' => ['required', 'string', 'max:600'],
                'portraitPath' => $text(),
                'portraitAlt' => $text(160),
                'primaryLabel' => $text(60),
                'primaryUrl' => $link,
                'cvLabel' => $text(60),
                'shippedTitle' => $text(80),
                'shippedNote' => $text(120),
                'shippedUrl' => $url,
            ],
            'availability' => [
                'enabled' => ['required', 'boolean'],
                'text' => $text(),
                'shortText' => $text(),
            ],
            'contact' => [
                'email' => ['required', ...SafeEmail::rules()],
                'phone' => $text(40),
                'whatsappUrl' => $url,
                'location' => $text(),
                'cvDownloadName' => ['nullable', 'string', 'max:120', 'regex:/^[A-Za-z0-9._-]+\.pdf$/'],
            ],
            'socials' => [
                '*' => ['array'],
                '*.label' => ['required', 'string', 'max:40'],
                '*.href' => ['required', 'string', 'max:255', 'regex:/^(https?:\/\/|mailto:)/i'],
            ],
            'appearance' => [
                'theme' => ['required', Rule::in(['light', 'dark', 'system'])],
                'accent' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            ],
            'sections' => collect(self::DEFAULTS['sections'])
                ->flatMap(fn ($fields, $section) => collect($fields)->mapWithKeys(fn ($v, $field) => $field === 'needs'
                    ? ["{$section}.needs" => ['nullable', 'array', 'max:12'], "{$section}.needs.*" => ['required', 'string', 'max:60']]
                    : ["{$section}.{$field}" => $text(600)]))
                ->all(),
            'seo' => [
                'titleSuffix' => $text(40),
                'shareImagePath' => $text(),
                ...collect(self::SEO_PAGES)->flatMap(fn ($page) => [
                    "{$page}.title" => ['required', 'string', 'max:70'],
                    "{$page}.description" => ['required', 'string', 'max:200'],
                ])->all(),
                // Just the token from the verification meta tag, not the whole tag.
                'googleVerification' => ['nullable', 'string', 'max:120', 'regex:/^[A-Za-z0-9_\-]+$/'],
                'bingVerification' => ['nullable', 'string', 'max:120', 'regex:/^[A-Za-z0-9_\-]+$/'],
                'gaMeasurementId' => ['nullable', 'string', 'regex:/^G-[A-Z0-9]{4,16}$/'],
            ],
            'ai' => [
                'provider' => ['required', Rule::in(['openai', 'anthropic'])],
                'openaiModel' => ['nullable', 'string', 'max:80', 'regex:/^[A-Za-z0-9._:\/-]+$/'],
                'anthropicModel' => ['nullable', 'string', 'max:80', 'regex:/^[A-Za-z0-9._:\/-]+$/'],
                'maxTokens' => ['required', 'integer', 'min:1000', 'max:128000'],
                'voice' => $text(8000),
                'openaiKey' => $text(300),
                'anthropicKey' => $text(300),
            ],
            'mail' => [
                'mailer' => ['nullable', Rule::in(['smtp', 'log'])],
                'host' => ['nullable', 'required_if:mailer,smtp', 'string', 'max:255'],
                'port' => ['nullable', 'required_if:mailer,smtp', 'integer', 'between:1,65535'],
                'username' => $text(),
                'password' => $text(),
                'encryption' => ['nullable', Rule::in(['tls', 'ssl', 'none'])],
                'fromAddress' => ['nullable', 'required_if:mailer,smtp', ...SafeEmail::rules()],
                'fromName' => $text(120),
                'contactRecipient' => ['nullable', ...SafeEmail::rules()],
            ],
        };
    }

    /** Deep merge of stored values over defaults, keeping only known keys. */
    private static function merge(array $defaults, mixed $stored): array
    {
        if (! is_array($stored)) {
            return $defaults;
        }

        if (array_is_list($defaults) && $defaults === []) {
            return array_values($stored);
        }

        $out = $defaults;
        foreach ($defaults as $field => $default) {
            if (! array_key_exists($field, $stored)) {
                continue;
            }
            $out[$field] = is_array($default) && ! array_is_list($default) && $default !== []
                ? self::merge($default, $stored[$field])
                : $stored[$field];
        }

        return $out;
    }

    /** Dot-path lookup on the merged settings, e.g. "sections.contact.needs". */
    public static function value(string $path, mixed $default = null): mixed
    {
        return Arr::get(self::all(), $path, $default);
    }
}
