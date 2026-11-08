<?php

namespace Database\Seeders\Concerns;

use App\Models\Media;
use App\Support\MediaUrl;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

trait ReadsSeedData
{
    /** The site's real content, exported from the original React data files. */
    protected function data(string $file): array
    {
        return json_decode(file_get_contents(database_path("seeders/data/{$file}.json")), true, flags: JSON_THROW_ON_ERROR);
    }

    /**
     * Copy a file from database/seeders/data/media (the images and CV from the
     * live charlestechy.com) to the media disk and record it in the library.
     * Returns the stored path.
     */
    protected function media(string $file, ?string $alt = null, string $folder = 'media/site'): string
    {
        $source = database_path("seeders/data/media/{$file}");
        throw_unless(is_file($source), new RuntimeException("Seed file missing: {$source}"));

        $disk = MediaUrl::disk();
        $path = "{$folder}/{$file}";
        Storage::disk($disk)->put($path, file_get_contents($source), 'public');

        Media::query()->updateOrCreate(['path' => $path], [
            'disk' => $disk,
            'mime' => mime_content_type($source) ?: 'application/octet-stream',
            'size' => filesize($source),
            'alt' => $alt,
        ]);

        return $path;
    }
}
