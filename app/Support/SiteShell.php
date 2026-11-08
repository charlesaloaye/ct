<?php

namespace App\Support;

/**
 * What every public page needs around its content: identity, header and
 * footer links, contact details and appearance. Shared with all Inertia pages
 * and cached with the rest of the public content.
 */
final class SiteShell
{
    public static function get(): array
    {
        return PublicCache::remember('shell', function () {
            $s = SiteSettings::all();
            $contact = $s['contact'];
            $availability = $s['availability'];
            $enabled = (bool) $availability['enabled'];

            return [
                'identity' => $s['identity'] + ['logoUrl' => MediaUrl::for($s['identity']['logoPath'])],
                'availability' => $enabled ? $availability['text'] : null,
                'availabilityShort' => $enabled ? $availability['shortText'] : null,
                'contact' => [
                    'email' => $contact['email'],
                    // The number itself never reaches the browser: WhatsApp links go through /whatsapp.
                    'whatsapp' => self::whatsappUrl($s) ? route('whatsapp') : null,
                    'location' => $contact['location'],
                    'cv' => filled($contact['cvPath']) ? route('cv') : null,
                ],
                'primaryCta' => self::primaryCta($s),
                'socials' => array_values(array_map(fn ($link) => self::isWhatsapp($link['href'] ?? '')
                    ? ['label' => $link['label'], 'href' => route('whatsapp')]
                    : $link, $s['socials'])),
                'appearance' => $s['appearance'],
                'seo' => ['titleSuffix' => $s['seo']['titleSuffix']],
            ];
        });
    }

    /** The main call to action ("Book a free 20-min call"): its own link, else WhatsApp, else email. */
    public static function primaryCta(array $s): ?array
    {
        $href = $s['hero']['primaryUrl']
            ?: (self::whatsappUrl($s) ? route('whatsapp') : null)
            ?: (filled($s['contact']['email']) ? 'mailto:'.$s['contact']['email'] : null);

        return $href && filled($s['hero']['primaryLabel']) ? ['label' => $s['hero']['primaryLabel'], 'href' => $href] : null;
    }

    /** The private WhatsApp link: the one in Contact settings, else a WhatsApp social link. */
    public static function whatsappUrl(?array $s = null): ?string
    {
        $s ??= SiteSettings::all();

        return $s['contact']['whatsappUrl']
            ?: collect($s['socials'])->first(fn ($link) => self::isWhatsapp($link['href'] ?? ''))['href'] ?? null;
    }

    private static function isWhatsapp(string $href): bool
    {
        return (bool) preg_match('#^https?://(wa\.me|api\.whatsapp\.com|(www\.)?whatsapp\.com)/#i', $href);
    }
}
