<?php

use App\Models\Article;

beforeEach(fn () => seedSite());

/** The JSON-LD @graph rendered in the page head. */
function schemaOf(string $html): array
{
    preg_match('#<script type="application/ld\+json">(.*?)</script>#s', $html, $m);

    return collect(json_decode($m[1] ?? '{}', true)['@graph'] ?? [])->keyBy('@type')->all();
}

it('describes the owner and the site on the homepage', function () {
    $g = schemaOf($this->get('/')->getContent());

    expect($g['WebSite']['url'])->toBe(url('/'))
        ->and($g['Person']['name'])->toBe('Charles Aloaye')
        ->and($g['Person']['alternateName'])->toBe(['CharlesTechy', 'Charles Aloaye Sedenu'])
        ->and($g['Person']['jobTitle'])->toBe(['Software Engineer', 'Technology Entrepreneur', 'SaaS Builder', 'TRCN-certified Educator'])
        ->and($g['Person']['knowsAbout'])->toContain('Laravel', 'FastAPI', 'React Native', 'PostgreSQL')
        ->and($g['Person']['sameAs'])->toContain('https://github.com/charlesaloaye')
        ->and(json_encode($g))->not->toContain('8137203803')->not->toContain('wa.me');
});

it('marks up articles with dates, author and breadcrumbs, plus article meta tags', function () {
    $article = Article::query()->where('slug', 'rescue-stalled-laravel-codebase')->sole();
    $html = $this->get('/blog/'.$article->slug)->getContent();
    $g = schemaOf($html);

    expect($g['BlogPosting']['headline'])->toBe($article->title)
        ->and($g['BlogPosting']['datePublished'])->toBe($article->published_at->toIso8601String())
        ->and($g['BlogPosting']['author']['name'])->toBe('Charles Aloaye')
        ->and(collect($g['BreadcrumbList']['itemListElement'])->pluck('name')->all())->toBe(['Home', 'Blog', $article->title]);
    expect($html)->toContain('property="article:published_time"')->toContain('name="twitter:title"');
});

it('marks up case studies and breadcrumbs on work pages', function () {
    $g = schemaOf($this->get('/work/delsu-bursary')->getContent());

    expect($g['CreativeWork']['sourceOrganization']['name'])->toBe('Delta State University')
        ->and(count($g['BreadcrumbList']['itemListElement']))->toBe(3);
    expect(schemaOf($this->get('/work')->getContent()))->toHaveKey('BreadcrumbList')
        ->and(schemaOf($this->get('/blog')->getContent()))->toHaveKey('BreadcrumbList');
});

it('cannot be broken out of by content containing a script tag', function () {
    $a = Article::factory()->create(['title' => 'Hack </script><script>alert(1)</script>']);

    $html = $this->get('/blog/'.$a->slug)->getContent();
    expect($html)->not->toContain('</script><script>alert(1)')
        ->and(schemaOf($html)['BlogPosting']['headline'])->toContain('</script>');
});

it('keeps the admin out of search entirely', function () {
    $html = $this->get('/admin/login')->getContent();
    expect($html)->toContain('noindex')->not->toContain('application/ld+json');
});

it('keeps search titles within what Google shows', function () {
    foreach (['/', '/work', '/blog', '/work/delsu-bursary', '/work/wavecrest', '/blog/rescue-stalled-laravel-codebase'] as $path) {
        preg_match('#<title inertia>(.*?)</title>#s', $this->get($path)->getContent(), $m);
        expect(mb_strlen(html_entity_decode($m[1])))->toBeLessThanOrEqual(65, $path);
    }
    $this->get('/blog')->assertSee('<title inertia>Blog - Charles Aloaye</title>', false);
});
