<?php

use App\Console\Commands\CheckLinks;
use App\Models\Event;
use App\Models\Message;
use Illuminate\Support\Facades\Cache;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    seedSite();
    actingAsAdmin();
});

it('builds the overview with counts, latest enquiries and stats', function () {
    Message::factory()->count(4)->create();
    Event::record('cv_download', '/cv');
    Event::record('page_view', '/');

    $this->get('/admin')->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('admin/Overview')
        ->where('counts.content.projects.total', 11)
        ->where('counts.content.projects.featured', 1)
        ->where('counts.content.articles.draft', 17)
        ->where('counts.unreadMessages', 4)
        ->has('latestMessages', 3)
        ->where('stats.cvDownloads', 1)
        ->where('stats.pageViews', 1)
        ->where('availability.enabled', true)
        ->where('adminCounts.unreadMessages', 4));
});

it('lists what is left to set up, each with a link to fix it', function () {
    config(['mail.default' => 'log', 'ai.openai.api_key' => null]);

    $this->get('/admin')->assertInertia(fn (Assert $page) => $page
        ->where('setup.0.href', route('admin.integrations.edit'))
        ->where('setup', fn ($items) => collect($items)->pluck('label')->contains(fn ($l) => str_contains($l, 'email service'))
            && collect($items)->pluck('label')->contains(fn ($l) => str_contains($l, 'AI key'))
            && collect($items)->pluck('label')->contains(fn ($l) => str_contains($l, 'two-factor'))));
});

it('flags dead links found by the weekly check', function () {
    Cache::forever(CheckLinks::CACHE_KEY, ['checkedAt' => now()->toIso8601String(), 'broken' => [
        ['url' => 'https://gone.example', 'label' => 'Project: Old one', 'href' => route('admin.projects.edit', 1), 'status' => 404],
    ]]);

    $this->get('/admin')->assertInertia(fn (Assert $page) => $page->where('setup.0.label', 'Dead link: https://gone.example (Project: Old one)'));
});
