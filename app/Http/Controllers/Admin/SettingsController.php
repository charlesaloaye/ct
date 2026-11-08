<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ValidatesSettings;
use App\Http\Controllers\Controller;
use App\Support\MediaUrl;
use App\Support\SiteSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/** Site settings: identity, hero, availability, contact, social links, CV and appearance. */
class SettingsController extends Controller
{
    use ValidatesSettings;

    public const GROUPS = ['identity', 'hero', 'availability', 'contact', 'socials', 'appearance'];

    public function edit(): Response
    {
        $s = SiteSettings::all();
        $cv = $s['contact']['cvPath'];

        return Inertia::render('admin/Settings', [
            'settings' => collect(self::GROUPS)->mapWithKeys(fn ($g) => [$g => $s[$g]])->all(),
            'portraitUrl' => MediaUrl::for($s['hero']['portraitPath']),
            'logoUrl' => MediaUrl::for($s['identity']['logoPath']),
            'cv' => [
                'name' => $cv ? basename(parse_url($cv, PHP_URL_PATH) ?: $cv) : null,
                'url' => $cv ? route('cv') : null,
            ],
            'meta' => ['title' => 'Site settings · Admin'],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        // The WhatsApp link follows the phone number unless a link is given.
        if (filled($request->input('contact.phone')) && blank($request->input('contact.whatsappUrl'))) {
            $request->merge(['contact' => [
                ...$request->input('contact'),
                'whatsappUrl' => 'https://wa.me/'.preg_replace('/\D+/', '', $request->input('contact.phone')),
            ]]);
        }

        $this->saveGroups($request, self::GROUPS);
        Inertia::flash('toast', 'Settings saved');

        return back();
    }

    /** The availability switch on the overview. */
    public function availability(Request $request): RedirectResponse
    {
        $request->validate(['enabled' => ['required', 'boolean']]);
        SiteSettings::save('availability', ['enabled' => $request->boolean('enabled')]);

        Inertia::flash('toast', $request->boolean('enabled') ? 'Availability badge is live' : 'Availability badge hidden');

        return back();
    }
}
