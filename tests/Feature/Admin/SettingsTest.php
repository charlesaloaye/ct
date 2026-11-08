<?php

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Stat;
use App\Support\SiteSettings;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    seedSite();
    actingAsAdmin();
});

function settingsPayload(array $overrides = []): array
{
    return array_replace_recursive(collect(App\Http\Controllers\Admin\SettingsController::GROUPS)
        ->mapWithKeys(fn ($g) => [$g => SiteSettings::group($g)])->all(), $overrides);
}

it('shows and saves site settings, and the public site follows', function () {
    $this->get('/admin/settings')->assertInertia(fn (Assert $page) => $page
        ->component('admin/Settings')
        ->where('settings.identity.name', 'Charles Aloaye Sedenu')
        ->where('cv.name', 'charlesaloaye.pdf'));

    $this->put('/admin/settings', settingsPayload([
        'hero' => ['headline' => 'I build *calm* software.'],
        'identity' => ['shortName' => 'Charles A.'],
        'appearance' => ['accent' => '#8AB4FF'],
    ]))->assertRedirect()->assertSessionHasNoErrors();

    $this->get('/')->assertInertia(fn (Assert $page) => $page
        ->where('home.hero.headline', 'I build *calm* software.')
        ->where('site.identity.shortName', 'Charles A.'))
        ->assertSee('--accent:#8AB4FF', false);
});

it('builds the WhatsApp link from the number when no link is given', function () {
    $this->put('/admin/settings', settingsPayload(['contact' => ['phone' => '+44 7700 900123', 'whatsappUrl' => '']]));

    expect(SiteSettings::value('contact.whatsappUrl'))->toBe('https://wa.me/447700900123');
});

it('saves the social links as an ordered list', function () {
    $this->put('/admin/settings', array_replace(settingsPayload(), ['socials' => [['label' => 'LinkedIn', 'href' => 'https://linkedin.com/in/x'], ['label' => 'GitHub', 'href' => 'https://github.com/x']]]));

    expect(SiteSettings::group('socials'))->toBe([['label' => 'LinkedIn', 'href' => 'https://linkedin.com/in/x'], ['label' => 'GitHub', 'href' => 'https://github.com/x']]);
});

it('validates settings', function (array $overrides, string $field) {
    $payload = settingsPayload($overrides);
    if (isset($overrides['socials'])) {
        $payload['socials'] = $overrides['socials'];
    }
    $this->put('/admin/settings', $payload)->assertSessionHasErrors($field);
})->with([
    [['identity' => ['name' => '']], 'identity.name'],
    [['hero' => ['headline' => '']], 'hero.headline'],
    [['hero' => ['primaryUrl' => 'javascript:alert(1)']], 'hero.primaryUrl'],
    [['contact' => ['email' => 'nope']], 'contact.email'],
    [['contact' => ['cvDownloadName' => '../etc/passwd']], 'contact.cvDownloadName'],
    [['appearance' => ['accent' => 'red']], 'appearance.accent'],
    [['appearance' => ['theme' => 'neon']], 'appearance.theme'],
    [['socials' => [['label' => 'X', 'href' => 'javascript:alert(1)']]], 'socials.0.href'],
]);

it('toggles the availability badge from the overview', function () {
    $this->patch('/admin/availability', ['enabled' => false])->assertRedirect();
    $this->get('/')->assertInertia(fn (Assert $page) => $page->where('site.availability', null));

    $this->patch('/admin/availability', ['enabled' => true]);
    $this->get('/')->assertInertia(fn (Assert $page) => $page->where('site.availability', 'Taking on 2 new projects for Q1 2027'));
});

it('edits every block of page copy and the SEO titles', function () {
    $sections = SiteSettings::group('sections');
    $sections['blog']['title'] = 'Field notes.';
    $sections['contact']['needs'] = ['Audit', 'Build'];
    $seo = SiteSettings::group('seo');
    $seo['blog']['title'] = 'Writing';

    $this->get('/admin/pages')->assertInertia(fn (Assert $page) => $page->component('admin/Pages')->has('sections.contact.needs', 6));
    $about = SiteSettings::group('about');
    $this->put('/admin/pages', ['about' => $about, 'sections' => $sections, 'seo' => $seo])->assertRedirect()->assertSessionHasNoErrors();

    $this->get('/blog')->assertInertia(fn (Assert $page) => $page
        ->where('sections.blog.title', 'Field notes.')
        ->where('meta.title', 'Writing - Charles Aloaye'));
    $this->get('/')->assertInertia(fn (Assert $page) => $page->where('home.sections.contact.needs', ['Audit', 'Build']));

    $seo['home']['title'] = '';
    $this->put('/admin/pages', ['about' => $about, 'sections' => $sections, 'seo' => $seo])->assertSessionHasErrors('seo.home.title');
});

it('edits the homepage lists: update, add, remove and reorder in one save', function () {
    $stats = Stat::query()->ordered()->get();
    $items = [
        ['id' => $stats[1]->id, 'value' => '600+', 'label' => 'Projects delivered'],
        ['id' => $stats[0]->id, 'value' => '10+', 'label' => 'Years shipping production code'],
        ['value' => '24h', 'label' => 'Reply time'],
    ];

    $this->get('/admin/homepage')->assertInertia(fn (Assert $page) => $page->component('admin/Lists')->has('lists.stats', 4)->has('lists.categories', 8));
    $this->put('/admin/homepage/stats', ['items' => $items])->assertRedirect()->assertSessionHasNoErrors();

    $this->get('/')->assertInertia(fn (Assert $page) => $page
        ->has('home.stats', 3)
        ->where('home.stats.0.v', '600+')
        ->where('home.stats.2.l', 'Reply time'));

    $this->put('/admin/homepage/stats', ['items' => [['value' => '', 'label' => 'x']]])->assertSessionHasErrors('items.0.value');
    $this->put('/admin/homepage/nope', ['items' => []])->assertNotFound();
});

it('keeps articles when their category is removed', function () {
    $keep = ArticleCategory::query()->ordered()->first();
    $article = Article::factory()->create(['category_id' => ArticleCategory::query()->ordered()->skip(1)->first()->id]);

    $this->put('/admin/homepage/categories', ['items' => [['id' => $keep->id, 'name' => $keep->name, 'tint' => '#ABCDEF']]])->assertSessionHasNoErrors();

    expect(ArticleCategory::query()->count())->toBe(1)
        ->and($keep->fresh()->tint)->toBe('#ABCDEF')
        ->and($article->fresh()->category_id)->toBeNull();
});

it('uploads images and enforces type and size limits', function () {
    $this->postJson('/admin/media', ['file' => UploadedFile::fake()->image('shot.jpg', 1200, 800)])
        ->assertCreated()->assertJsonPath('data.mime', 'image/jpeg');
    $path = App\Models\Media::query()->latest('id')->first()->path;
    Storage::disk('public')->assertExists($path);

    $this->postJson('/admin/media', ['file' => UploadedFile::fake()->create('x.svg', 1, 'image/svg+xml')])->assertUnprocessable();
    $this->postJson('/admin/media', ['file' => UploadedFile::fake()->image('big.jpg')->size(6000)])->assertUnprocessable();
});

it('replaces the CV at once and serves the new file', function () {
    $this->postJson('/admin/cv', ['file' => UploadedFile::fake()->image('cv.jpg')])->assertUnprocessable();
    $this->postJson('/admin/cv', ['file' => UploadedFile::fake()->create('new-cv.pdf', 50, 'application/pdf')])->assertCreated();

    $path = SiteSettings::value('contact.cvPath');
    expect($path)->toStartWith('cv/')->not->toBe('cv/charlesaloaye.pdf')
        ->and(SiteSettings::value('contact.email'))->toBe('hello@charlestechy.com');
    Storage::disk('public')->assertExists($path);
    $this->get('/cv')->assertOk();
});

it('saves, clears and shows client logos from the homepage lists', function () {
    $clients = App\Models\Client::query()->ordered()->get();
    $items = $clients->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'logoPath' => $c->logo_path])->all();
    $items[4]['logoPath'] = 'media/clients/rccg.png';
    $items[0]['logoPath'] = null;

    $this->put('/admin/homepage/clients', ['items' => $items])->assertSessionHasNoErrors();

    $this->get('/')->assertInertia(fn (Assert $page) => $page
        ->where('home.clients.0.logoUrl', null)
        ->where('home.clients.4.logoUrl', fn ($u) => str_contains($u, 'media/clients/rccg.png')));
    $this->get('/admin/homepage')->assertInertia(fn (Assert $page) => $page->where('lists.clients.4.logoPath', 'media/clients/rccg.png'));
});
