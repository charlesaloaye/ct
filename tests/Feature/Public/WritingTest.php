<?php

use App\Models\Writing;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(fn () => seedSite());

it('lists articles published elsewhere on the blog, newest first, each linking to its source', function () {
    $this->get('/blog')->assertInertia(fn (Assert $page) => $page
        ->has('writings', 17)
        ->where('writings.0.publication', 'DEV (Apify)')
        ->where('writings.0.href', fn ($u) => str_starts_with($u, 'https://dev.to/apify/'))
        ->where('writings', fn ($w) => collect($w)->pluck('publication')->unique()->sort()->values()->all() === ['DEV', 'DEV (Apify)', 'Hashnode', 'Medium']));
});

it('hides a hidden link and needs a unique source URL', function () {
    actingAsAdmin();
    $w = Writing::query()->first();
    $this->patch("/admin/writings/{$w->id}/visibility");
    $this->get('/blog')->assertInertia(fn (Assert $page) => $page->has('writings', 16));

    $this->post('/admin/writings', ['title' => 'Dup', 'publication' => 'Medium', 'url' => Writing::query()->skip(1)->first()->url])->assertSessionHasErrors('url');
    $this->post('/admin/writings', ['title' => 'New post', 'publication' => 'Medium'])->assertSessionHasErrors('url');
    $this->post('/admin/writings', ['title' => 'New post', 'publication' => 'Medium', 'url' => 'https://charlestechy.medium.com/new', 'status' => 'Published'])->assertSessionHasNoErrors();
});

it('includes LinkedIn, YouTube and Medium in the profiles Google reads', function () {
    preg_match('#<script type="application/ld\+json">(.*?)</script>#s', $this->get('/')->getContent(), $m);
    $person = collect(json_decode($m[1], true)['@graph'])->firstWhere('@type', 'Person');

    expect($person['sameAs'])->toContain(
        'https://www.linkedin.com/in/charles-sedenu/',
        'https://www.youtube.com/channel/UCz5nyN0Og65kX8dAYg7dI0A',
        'https://charlestechy.medium.com',
        'https://hashnode.com/@charlestechy',
        'https://www.threads.com/@charlestechy',
        'https://apify.com/charlestechy',
    );
});
