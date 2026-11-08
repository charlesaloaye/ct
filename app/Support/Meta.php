<?php

namespace App\Support;

/** Page title, description and share image for the <head> (rendered on the server for crawlers). */
final class Meta
{
    public const MAX_TITLE = 65;

    public static function make(?string $title, ?string $description = null, ?string $image = null, string $type = 'website', bool $suffix = true): array
    {
        $settings = SiteSettings::all();
        $name = $settings['identity']['name'] ?: config('app.name');
        $titleSuffix = $settings['seo']['titleSuffix'];

        $full = $title ?: $name;
        // Google shows about 60-65 characters; drop the suffix rather than have the title cut off.
        $suffixName = trim((string) $titleSuffix, " -–|·");
        if ($suffix && $title && filled($titleSuffix) && ! str_ends_with($title, $titleSuffix)
            && ($suffixName === '' || ! str_contains($title, $suffixName))
            && mb_strlen($title.' '.$titleSuffix) <= self::MAX_TITLE) {
            $full .= ' '.$titleSuffix;
        }

        $image = $image ?: MediaUrl::for($settings['seo']['shareImagePath']) ?: MediaUrl::for($settings['hero']['portraitPath']);

        return [
            'title' => $full,
            'description' => $description ?: null,
            // Share previews need an absolute URL.
            'image' => $image && str_starts_with($image, '/') && ! str_starts_with($image, '//') ? url($image) : $image,
            'type' => $type,
            'url' => url()->current(),
        ];
    }
}
