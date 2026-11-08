<?php

namespace App\Http\Controllers\Admin;

use App\Console\Commands\CheckLinks;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\MessageResource;
use App\Models\Event;
use App\Models\Message;
use App\Support\AdminCounts;
use App\Support\SiteSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class OverviewController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $since = now()->subDays(30);
        $events = Event::query()->where('created_at', '>=', $since);

        return Inertia::render('admin/Overview', [
            'counts' => AdminCounts::get(),
            'latestMessages' => MessageResource::collection(Message::query()->inbox()->latest()->latest('id')->limit(3)->get())->resolve($request),
            'stats' => [
                'cvDownloads' => (clone $events)->where('type', 'cv_download')->count(),
                'pageViews' => (clone $events)->where('type', 'page_view')->count(),
                'outboundClicks' => (clone $events)->where('type', 'outbound_click')->count(),
            ],
            'availability' => SiteSettings::group('availability'),
            'setup' => $this->setupChecklist(),
            'meta' => ['title' => 'Overview · Admin'],
        ]);
    }

    /** What still needs doing before the site is fully working. Each item links to where it is fixed. */
    private function setupChecklist(): array
    {
        $s = SiteSettings::all();
        $mailer = config('mail.default');

        $dead = collect(Cache::get(CheckLinks::CACHE_KEY)['broken'] ?? [])
            ->map(fn ($l) => ['label' => "Dead link: {$l['url']} ({$l['label']})", 'href' => $l['href']]);

        return array_values(array_filter([
            ...$dead->all(),
            in_array($mailer, ['log', 'array'], true)
                ? ['label' => 'Connect an email service so contact messages, replies and newsletters are delivered', 'href' => route('admin.integrations.edit')] : null,
            blank(config('ai.'.config('ai.provider').'.api_key')) || blank(config('ai.'.config('ai.provider').'.model'))
                ? ['label' => 'Add an AI key and model to use the writing assistant', 'href' => route('admin.integrations.edit')] : null,
            blank($s['contact']['cvPath'])
                ? ['label' => 'Upload your CV so "Download CV" works', 'href' => route('admin.settings.edit')] : null,
            blank($s['hero']['portraitPath'])
                ? ['label' => 'Upload a portrait for the homepage', 'href' => route('admin.settings.edit')] : null,
            ! request()->user()->hasTwoFactorEnabled()
                ? ['label' => 'Turn on two-factor sign-in', 'href' => route('admin.account.edit')] : null,
        ]));
    }
}
