<?php

use App\Models\Article;
use App\Models\Event;
use App\Support\SiteSettings;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('records page views and outbound clicks without personal data', function () {
    $this->postJson('/events', ['type' => 'page_view', 'path' => '/blog?email=a@b.c#x'])->assertNoContent();
    $this->postJson('/events', ['type' => 'outbound_click', 'path' => '/', 'href' => 'https://frontstore.app', 'label' => 'FrontStore'])->assertNoContent();

    expect(Event::query()->pluck('path')->all())->toBe(['/blog', '/'])
        ->and(Event::query()->latest('id')->first()->meta)->toBe(['href' => 'https://frontstore.app', 'label' => 'FrontStore']);
});

it('rejects unknown events and outbound clicks without a link', function () {
    $this->postJson('/events', ['type' => 'hack'])->assertUnprocessable();
    $this->postJson('/events', ['type' => 'outbound_click'])->assertUnprocessable();
});

it('rate limits events', function () {
    config(['site.limits.events_per_minute' => 2]);
    $this->postJson('/events', ['type' => 'page_view'])->assertNoContent();
    $this->postJson('/events', ['type' => 'page_view'])->assertNoContent();
    $this->postJson('/events', ['type' => 'page_view'])->assertTooManyRequests();
});

it('streams the uploaded CV with the chosen file name and counts the download', function () {
    Storage::fake('public');
    Storage::disk('public')->put('cv/cv.pdf', UploadedFile::fake()->create('cv.pdf', 10, 'application/pdf')->getContent());
    SiteSettings::save('contact', ['cvPath' => 'cv/cv.pdf', 'cvDownloadName' => 'jane-doe-cv.pdf']);

    $response = $this->get('/cv')->assertOk()->assertHeader('Content-Type', 'application/pdf');
    expect($response->headers->get('Content-Disposition'))->toContain('jane-doe-cv.pdf')
        ->and(Event::query()->where('type', 'cv_download')->count())->toBe(1);
});

it('redirects to an external CV url and still counts it', function () {
    SiteSettings::save('contact', ['cvPath' => 'https://cdn.example.com/cv.pdf']);

    $this->get('/cv')->assertRedirect('https://cdn.example.com/cv.pdf');
    expect(Event::query()->where('type', 'cv_download')->count())->toBe(1);
});

it('404s when there is no CV, and the site hides the button', function () {
    $this->get('/cv')->assertNotFound();
    $this->get('/')->assertInertia(fn ($page) => $page->where('site.contact.cv', null));
});

it('lists home, work, blog, case studies and live articles in the sitemap', function () {
    seedSite();
    Article::factory()->draft()->create(['slug' => 'secret-draft']);

    $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
        ->assertSee(route('work.show', 'delsu-bursary'), false)
        ->assertSee(route('blog.show', 'rescue-stalled-laravel-codebase'), false)
        ->assertDontSee('secret-draft')
        ->assertDontSee('codebase-audit-checklist');
});

it('publishes an RSS feed of live articles and a robots.txt that points at the sitemap', function () {
    seedSite();

    $this->get('/feed.xml')->assertOk()->assertSee('<title>How I rescue', false)->assertDontSee('codebase-audit-checklist');
    $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /admin')->assertSee('Sitemap: '.route('sitemap'));
});
