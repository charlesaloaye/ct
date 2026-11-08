<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Support\MediaUrl;
use App\Support\SiteSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** "Download CV": serves the PDF uploaded in Admin → Site settings and counts the download. */
class CvController extends Controller
{
    public function __invoke(): StreamedResponse|RedirectResponse
    {
        $contact = SiteSettings::group('contact');
        $path = $contact['cvPath'];
        abort_if(blank($path), 404);

        // A CV hosted elsewhere is redirected to rather than streamed.
        if (Str::startsWith($path, ['http://', 'https://'])) {
            Event::record('cv_download', '/cv');

            return redirect()->away($path);
        }

        $disk = Storage::disk(MediaUrl::disk());
        abort_unless($disk->exists($path), 404);

        Event::record('cv_download', '/cv');

        $name = $contact['cvDownloadName'] ?: Str::slug(SiteSettings::value('identity.name') ?: 'cv').'-cv.pdf';

        return $disk->response($path, $name, [
            'Content-Type' => 'application/pdf',
            'Cache-Control' => 'no-store',
        ], 'inline');
    }
}
