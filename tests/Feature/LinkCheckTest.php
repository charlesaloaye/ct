<?php

use App\Console\Commands\CheckLinks;
use App\Models\Product;
use App\Models\Project;
use App\Support\SiteSettings;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request as Psr7Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

it('flags dead outbound links but not sites that refuse robots', function () {
    Project::factory()->create(['url' => 'https://gone.example', 'title' => 'Gone']);
    Project::factory()->create(['url' => 'https://ok.example']);
    Project::factory()->status('draft')->create(['url' => 'https://draft.example']);
    Product::factory()->create(['url' => 'https://down.example', 'name' => 'Down']);
    SiteSettings::save('socials', [['label' => 'Messenger', 'href' => 'https://m.me/someone'], ['label' => 'LinkedIn', 'href' => 'https://www.linkedin.com/in/someone/']]);

    Http::fake([
        'gone.example*' => Http::response('', 404),
        'ok.example*' => Http::response('ok'),
        'down.example*' => fn ($request) => throw new ConnectException('Could not resolve host', new Psr7Request('GET', 'https://down.example')),
        'm.me*' => Http::response('', 400),
        // LinkedIn refuses robots (no answer, or its own 999 code): never reported as dead.
        'www.linkedin.com*' => fn ($request) => throw new ConnectException('Refused', new Psr7Request('GET', 'https://www.linkedin.com/in/someone/')),
    ]);

    $this->artisan('site:check-links')->assertSuccessful();

    $broken = collect(Cache::get(CheckLinks::CACHE_KEY)['broken']);
    expect($broken->pluck('url')->sort()->values()->all())->toBe(['https://down.example', 'https://gone.example'])
        ->and($broken->firstWhere('url', 'https://gone.example')['label'])->toBe('Project: Gone');
    Http::assertNotSent(fn ($r) => str_contains($r->url(), 'draft.example'));
});

it('is scheduled weekly', function () {
    $this->artisan('schedule:list')->expectsOutputToContain('site:check-links');
});
