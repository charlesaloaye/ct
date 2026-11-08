<?php

use App\Models\Appearance;
use App\Models\Product;
use App\Models\Venture;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(fn () => actingAsAdmin());

it('creates a venture with a logo, links a product to it, and shows both publicly', function () {
    $this->post('/admin/ventures', ['name' => 'Test Ventures LTD', 'role' => 'Founder & CEO', 'isFounder' => true, 'url' => 'https://example.com', 'logoPath' => 'media/x/logo.png', 'status' => 'Published'])
        ->assertRedirect()->assertSessionHasNoErrors();
    $venture = Venture::query()->sole();
    expect($venture->slug)->toBe('test-ventures-ltd')->and($venture->is_founder)->toBeTrue();

    $this->get('/admin/products/create')->assertInertia(fn (Assert $page) => $page->where('options.ventures.0.name', 'Test Ventures LTD'));
    $this->post('/admin/products', ['name' => 'Widget', 'ventureId' => $venture->id, 'logoPath' => 'media/x/w.png', 'url' => 'https://widget.example', 'status' => 'Published'])->assertSessionHasNoErrors();

    $this->get('/ventures')->assertInertia(fn (Assert $page) => $page
        ->where('ventures.0.name', 'Test Ventures LTD')
        ->where('ventures.0.products.0.name', 'Widget')
        ->where('ventures.0.logoUrl', fn ($u) => str_contains($u, 'media/x/logo.png')));
    expect(Product::query()->sole()->venture_id)->toBe($venture->id);

    $this->delete("/admin/ventures/{$venture->id}")->assertRedirect('/admin/ventures');
    expect(Product::query()->sole()->venture_id)->toBeNull();
});

it('requires a role for ventures and a verifiable link for coverage', function () {
    $this->post('/admin/ventures', ['name' => 'X'])->assertSessionHasErrors('role');
    $this->post('/admin/appearances', ['title' => 'Interview', 'outlet' => 'Paper', 'kind' => 'interview'])->assertSessionHasErrors('url');
    $this->post('/admin/appearances', ['title' => 'Interview', 'outlet' => 'Paper', 'kind' => 'rumour', 'url' => 'https://x.example'])->assertSessionHasErrors('kind');
});

it('adds, hides and deletes press coverage', function () {
    $this->post('/admin/appearances', ['title' => 'Founder interview', 'outlet' => 'TechCabal', 'kind' => 'interview', 'url' => 'https://techcabal.example/a', 'publishedOn' => '2026-10-01', 'status' => 'Published'])->assertSessionHasNoErrors();
    $a = Appearance::query()->sole();
    $this->get('/media')->assertInertia(fn (Assert $page) => $page->where('appearances.0.title', 'Founder interview'));

    $this->patch("/admin/appearances/{$a->id}/visibility");
    $this->get('/media')->assertInertia(fn (Assert $page) => $page->has('appearances', 0));
    $this->delete("/admin/appearances/{$a->id}")->assertRedirect('/admin/appearances');
});

it('saves the about biography, standard bios and verification codes from Pages & SEO', function () {
    seedSite();
    $s = App\Support\SiteSettings::all();
    $about = ['body' => "First paragraph.\n\nSecond paragraph."] + $s['about'];
    $seo = ['googleVerification' => 'tok_123-ABC', 'gaMeasurementId' => 'G-ABCD1234'] + $s['seo'];

    $this->put('/admin/pages', ['about' => $about, 'sections' => $s['sections'], 'seo' => $seo])->assertSessionHasNoErrors();
    $this->get('/about')->assertInertia(fn (Assert $page) => $page->where('about.body', "First paragraph.\n\nSecond paragraph."));

    $this->put('/admin/pages', ['about' => $about, 'sections' => $s['sections'], 'seo' => ['gaMeasurementId' => 'UA-123'] + $seo])->assertSessionHasErrors('seo.gaMeasurementId');
    $this->put('/admin/pages', ['about' => $about, 'sections' => $s['sections'], 'seo' => ['googleVerification' => '<meta name="x">'] + $seo])->assertSessionHasErrors('seo.googleVerification');
});
