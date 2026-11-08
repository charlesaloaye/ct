<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Google asks for profile photos in 1:1, 4:3 and 16:9 (at least 50,000 pixels
 * each). These are cut from the uploaded portrait, centred on the top third
 * where the face usually is, and cached until the portrait changes.
 */
final class ProfileImages
{
    private const RATIOS = ['1x1' => [1, 1], '4x3' => [4, 3], '16x9' => [16, 9]];

    /** @return list<string> public URLs */
    public static function for(?string $portraitPath): array
    {
        if (blank($portraitPath) || ! function_exists('imagecreatefromstring')) {
            return [];
        }

        $disk = Storage::disk(MediaUrl::disk());
        if (! $disk->exists($portraitPath)) {
            return [];
        }

        $hash = substr(md5($portraitPath.$disk->lastModified($portraitPath)), 0, 10);
        $urls = [];

        foreach (self::RATIOS as $name => [$w, $h]) {
            $path = "media/profile/{$hash}-{$name}.jpg";
            if (! $disk->exists($path) && ! self::make($disk->get($portraitPath), $w / $h, $disk, $path)) {
                continue;
            }
            $urls[] = MediaUrl::for($path);
        }

        return $urls;
    }

    private static function make(string $bytes, float $ratio, $disk, string $path): bool
    {
        try {
            $src = imagecreatefromstring($bytes);
            if (! $src) {
                return false;
            }
            [$sw, $sh] = [imagesx($src), imagesy($src)];

            // Largest crop of this ratio that fits; faces sit near the top, so bias upward.
            $cw = $sw;
            $ch = (int) round($sw / $ratio);
            if ($ch > $sh) {
                $ch = $sh;
                $cw = (int) round($sh * $ratio);
            }
            $x = (int) (($sw - $cw) / 2);
            $y = (int) max(0, min($sh - $ch, ($sh - $ch) * 0.08));

            $outW = min($cw, 1200);
            $outH = (int) round($outW / $ratio);
            $dst = imagecreatetruecolor($outW, $outH);
            imagecopyresampled($dst, $src, 0, 0, $x, $y, $outW, $outH, $cw, $ch);

            ob_start();
            imagejpeg($dst, null, 85);
            $disk->put($path, ob_get_clean(), 'public');

            return $outW * $outH >= 50000;
        } catch (Throwable $e) {
            report($e);

            return false;
        }
    }
}
