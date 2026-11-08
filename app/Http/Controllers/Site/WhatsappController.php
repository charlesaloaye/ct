<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Support\SiteShell;
use Illuminate\Http\RedirectResponse;

/**
 * "Message me on WhatsApp": sends the visitor to WhatsApp without the phone
 * number ever appearing in the site's pages or links.
 */
class WhatsappController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        $url = SiteShell::whatsappUrl();
        abort_if(blank($url), 404);

        Event::record('outbound_click', '/whatsapp', ['label' => 'WhatsApp']);

        return redirect()->away($url)->header('X-Robots-Tag', 'noindex, nofollow');
    }
}
