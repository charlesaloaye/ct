<?php

use App\Models\Article;
use App\Models\Project;
use App\Models\Service;
use App\Support\PublicCache;

it('generates unique slugs from titles', function () {
    $a = Article::factory()->draft()->create(['title' => 'Hello World']);
    $b = Article::factory()->draft()->create(['title' => 'Hello World']);

    expect($a->slug)->toBe('hello-world')
        ->and($b->slug)->toBe('hello-world-2');
});

it('re-slugs drafts but freezes the slug once published', function () {
    $draft = Article::factory()->draft()->create(['title' => 'First title']);
    $draft->update(['title' => 'Second title']);
    expect($draft->fresh()->slug)->toBe('second-title');

    $live = Article::factory()->create(['title' => 'Live title']);
    $live->update(['title' => 'Renamed live title']);
    expect($live->fresh()->slug)->toBe('live-title');
});

it('freezes project slugs once they leave draft', function () {
    $project = Project::factory()->create(['title' => 'Old name', 'status' => 'published']);
    $project->update(['title' => 'New name']);

    expect($project->fresh()->slug)->toBe('old-name');
});

it('computes read minutes from the body at 200 wpm', function () {
    $article = Article::factory()->create([
        'body' => [['type' => 'p', 'text' => str_repeat('word ', 450)], ['type' => 'h2', 'text' => 'A heading']],
    ]);

    expect($article->read_minutes)->toBe(3);

    $article->update(['body' => [['type' => 'p', 'text' => 'short']]]);
    expect($article->fresh()->read_minutes)->toBe(1);
});

it('keeps an explicit read time when one is given', function () {
    $article = Article::factory()->create(['read_minutes' => 8, 'body' => [['type' => 'p', 'text' => 'tiny']]]);

    expect($article->read_minutes)->toBe(8);
});

it('turns a future publish into a scheduled article', function () {
    $article = Article::factory()->create(['status' => 'published', 'published_at' => now()->addDay()]);

    expect($article->status)->toBe('scheduled');
});

it('stamps published_at when publishing without a date', function () {
    $article = Article::factory()->create(['status' => 'published', 'published_at' => null]);

    expect($article->published_at)->not->toBeNull();
});

it('fills the domain from the url', function () {
    $project = Project::factory()->create(['url' => 'https://www.eduabroad.ng/', 'domain' => null]);

    expect($project->domain)->toBe('eduabroad.ng');
});

it('assigns the next sort order on create', function () {
    $a = Service::factory()->create();
    $b = Service::factory()->create();

    expect($b->sort_order)->toBe($a->sort_order + 1);
});

it('busts the public cache on any content write', function () {
    $before = PublicCache::key('site');
    Service::factory()->create();

    expect(PublicCache::key('site'))->not->toBe($before);
});
