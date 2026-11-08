<?php

use App\Models\Service;
use App\Support\PublicCache;
use Illuminate\Support\Facades\Cache;

it('caches public pages and busts them on any content write', function () {
    seedSite();
    $this->get('/')->assertOk();
    expect(Cache::has(PublicCache::key('home')))->toBeTrue();

    Service::query()->first()->update(['title' => 'Something new']);

    expect(Cache::has(PublicCache::key('home')))->toBeFalse();
    $this->get('/')->assertInertia(fn ($page) => $page->where('home.services.0.title', 'Something new'));
});
