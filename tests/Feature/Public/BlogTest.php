<?php

use App\Models\Article;
use App\Models\ArticleCategory;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(fn () => seedSite());

it('lists only written articles, with the categories that have something to read', function () {
    $this->get('/blog')->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Blog')
        ->has('posts', 1)
        ->where('posts.0.slug', 'rescue-stalled-laravel-codebase')
        ->where('posts.0.featured', true)
        ->has('categories', 1)
        ->where('sections.blog.title', 'Notes from shipping software in Lagos.')
        ->where('subscriptionNote', null));

    expect(Article::query()->count())->toBe(18)
        ->and(Article::query()->where('status', 'draft')->count())->toBe(17);
});

it('never lists drafts, hidden or future articles', function () {
    $cat = ArticleCategory::factory()->create();
    Article::factory()->draft()->create(['category_id' => $cat->id, 'title' => 'Draft post']);
    Article::factory()->status('hidden')->create(['category_id' => $cat->id, 'title' => 'Hidden post']);
    Article::factory()->create(['category_id' => $cat->id, 'title' => 'Future post', 'published_at' => now()->addDay()]);
    Article::factory()->create(['category_id' => $cat->id, 'title' => 'Live post']);

    $titles = collect($this->get('/blog')->viewData('page')['props']['posts'])->pluck('title');

    expect($titles)->toContain('Live post')->not->toContain('Draft post')->not->toContain('Hidden post')->not->toContain('Future post');
});

it('shows an article with body, meta and related posts', function () {
    $cat = Article::query()->where('slug', 'rescue-stalled-laravel-codebase')->sole()->category_id;
    $other = Article::factory()->create(['category_id' => $cat, 'title' => 'Another rescue']);

    $this->get('/blog/rescue-stalled-laravel-codebase')->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('BlogPost')
        ->where('post.slug', 'rescue-stalled-laravel-codebase')
        ->has('post.body')
        ->where('related.0.slug', $other->slug)
        ->where('meta.type', 'article'));
});

it('uses the SEO title and description when set', function () {
    $article = Article::factory()->create(['seo_title' => 'Custom SEO title', 'seo_description' => 'Custom description']);

    $this->get("/blog/{$article->slug}")->assertInertia(fn (Assert $page) => $page
        ->where('meta.title', 'Custom SEO title - Charles Aloaye')
        ->where('meta.description', 'Custom description'));
});

it('404s for drafts', function () {
    $this->get('/blog/codebase-audit-checklist')->assertNotFound();
});

it('explains the subscription link result', function (string $state, string $note) {
    $this->get("/blog?subscription={$state}")->assertInertia(fn (Assert $page) => $page->where('subscriptionNote', $note));
})->with([
    ['confirmed', "You're on the list - thanks!"],
    ['removed', "You've been unsubscribed. Sorry to see you go."],
    ['invalid', 'That link has expired or was already used.'],
]);
