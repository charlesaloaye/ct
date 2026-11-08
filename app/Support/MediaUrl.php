<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaUrl
{
    public static function disk(): string
    {
        return config('filesystems.media_disk', 'public');
    }

    /** Turn a stored path into a public URL. Absolute URLs pass through. */
    public static function for(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://', '//'])) {
            return $path;
        }

        return Storage::disk(self::disk())->url($path);
    }
}
