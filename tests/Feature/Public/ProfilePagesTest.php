<?php

use App\Models\Appearance;
use App\Models\Venture;
use App\Support\SiteSettings;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(fn () => seedSite());

function graphOf(string $html): array
{
    preg_match('#<script type="application/ld\+json">(.*?)</script>#s', $html, $m);

    return json_decode($m[1] ?? '{}', true)['@graph'] ?? [];
}

it('serves every profile page with its own title, copy and one h1-worthy heading', function (string $path, string $component, string $title) {
    $this->get($path)->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component($component)
        ->where('meta.title', $title)
        ->has('copy.title'));
})->with([
    ['/about', 'About', 'About Charles Aloaye'],
    ['/experience', 'Experience', 'Experience - Charles Aloaye'],
    ['/ventures', 'Ventures', 'Ventures - Charles Aloaye'],
    ['/products', 'Products', 'Products - Charles Aloaye'],
    ['/expertise', 'Expertise', 'Expertise - Charles Aloaye'],
    ['/media', 'Media', 'Media and press - Charles Aloaye'],
    ['/contact', 'Contact', 'Contact - Charles Aloaye'],
]);

it('marks the About page up as a ProfilePage for one Person with a stable id', function () {
    $graph = collect(graphOf($this->get('/about')->getContent()));
    $profile = $graph->firstWhere('@type', 'ProfilePage');
    $person = $profile['mainEntity'];

    expect($person['@id'])->toBe(url('/').'#person')
        ->and($person['name'])->toBe('Charles Aloaye')
        ->and($person['alternateName'])->toContain('CharlesTechy')
        ->and($person['hasCredential'][0]['recognizedBy']['alternateName'])->toBe('TRCN')
        ->and($person['sameAs'])->toContain('https://github.com/charlesaloaye', 'https://www.facebook.com/sedenu.charles')
        ->and($person['image'])->toHaveCount(3)
        ->and($profile['dateModified'])->not->toBeNull();

    // The same @id on the homepage, so both pages describe one entity.
    $home = collect(graphOf($this->get('/')->getContent()))->firstWhere('@type', 'Person');
    expect($home['@id'])->toBe($person['@id']);
});

it('describes each venture as an Organization with the right relationship', function () {
    $orgs = collect(graphOf($this->get('/ventures')->getContent()))->where('@type', 'Organization')->keyBy('name');

    expect($orgs->keys()->all())->toBe(['Built Different LTD', 'Hamplus Technologies', 'Gen6ixx LTD'])
        ->and($orgs['Built Different LTD']['founder']['@id'])->toBe(url('/').'#person')
        ->and(collect($orgs['Built Different LTD']['brand'])->pluck('name')->all())->toContain('Frontstore', 'DelsuTools', 'UniAdvisor', 'JAMB Advisor')
        ->and($orgs['Hamplus Technologies'])->toHaveKey('employee')->not->toHaveKey('founder');
});

it('lists products with their company and real logos, and hides hidden ones', function () {
    $this->get('/products')->assertInertia(fn (Assert $page) => $page
        ->has('products', 7)
        ->where('products.0.name', 'Frontstore')
        ->where('products.0.venture.name', 'Built Different LTD')
        ->where('products.0.logoUrl', fn ($u) => str_contains($u, 'product-frontstore.png')));

    Venture::query()->where('slug', 'gen6ixx')->update(['status' => 'hidden']);
    $this->get('/ventures')->assertInertia(fn (Assert $page) => $page->has('ventures', 2));
});

it('shows coverage only when it exists, and always offers the press kit', function () {
    Appearance::query()->delete();
    $this->get('/media')->assertInertia(fn (Assert $page) => $page
        ->has('appearances', 0)
        ->where('about.bioShort', fn ($b) => str_contains($b, 'TRCN-certified educator')));

    Appearance::factory()->create(['title' => 'Building SaaS from Lagos', 'outlet' => 'Tech Podcast', 'url' => 'https://example.com/ep1', 'status' => 'published']);
    $this->get('/media')->assertInertia(fn (Assert $page) => $page->where('appearances.0.outlet', 'Tech Podcast'));
});

it('seeds the DELSU Tech Invasion talk with a working source link', function () {
    $this->seed(\Database\Seeders\AppearanceSeeder::class);
    $this->get('/media')->assertInertia(fn (Assert $page) => $page
        ->where('appearances', fn ($a) => collect($a)->contains(fn ($x) => $x['title'] === 'Speaker at DELSU Tech Invasion 1.0'
            && $x['kind'] === 'talk' && $x['href'] === 'https://blog.hamplustech.com/delsu_tech_invasion_1-0/')));
});

it('adds verification tags and analytics only when set, never in the admin', function () {
    $html = $this->get('/')->getContent();
    expect($html)->not->toContain('google-site-verification')->not->toContain('googletagmanager');

    $seo = SiteSettings::group('seo');
    SiteSettings::save('seo', ['googleVerification' => 'abc123XYZ', 'bingVerification' => 'BING99', 'gaMeasurementId' => 'G-TEST1234'] + $seo);

    expect($this->get('/')->getContent())->toContain('<meta name="google-site-verification" content="abc123XYZ">')
        ->toContain('<meta name="msvalidate.01" content="BING99">')
        ->toContain('googletagmanager.com/gtag/js?id=G-TEST1234');
    expect($this->get('/admin/login')->getContent())->not->toContain('googletagmanager')->not->toContain('google-site-verification');
});

it('lists every profile page in the sitemap and links them from the footer', function () {
    $xml = $this->get('/sitemap.xml')->getContent();
    foreach (['about', 'experience', 'ventures', 'products', 'expertise', 'media', 'contact.page'] as $route) {
        expect($xml)->toContain(route($route));
    }
});
