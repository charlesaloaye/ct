<?php

use App\Models\Project;
use App\Models\Testimonial;
use App\Support\SiteSettings;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(fn () => seedSite());

it('renders the homepage with the real content', function () {
    $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Home')
        ->where('home.hero.headline', "I ship the software other teams couldn't finish.")
        ->where('home.projects.0.slug', 'delsu-bursary')
        ->where('home.projects.0.featured', true)
        ->where('home.projectCount', 10)
        ->has('home.projects', 5)
        ->has('home.stats', 4)
        ->has('home.clients', 8)
        ->has('home.products', 7)
        ->where('home.products.0.venture.name', 'Built Different LTD')
        ->has('home.services', 7)
        ->where('home.services.6.title', 'Mentorship & training')
        ->where('site.identity.role', 'Software engineer · Founder · Educator')
        ->has('home.process', 4)
        ->has('home.rescue', 3)
        ->has('home.stack', 7)
        ->where('home.stack.4', ['label' => 'Databases', 'items' => ['PostgreSQL', 'MySQL', 'MongoDB', 'Prisma']])
        ->where('home.stack.1', ['label' => 'Python', 'items' => ['FastAPI', 'SQLAlchemy']])
        ->where('home.stats.1.v', '50+')
        ->where('home.stats.2.v', '30+')
        ->where('home.clients.0.name', 'Delta State University')
        ->where('home.clients.0.logoUrl', fn ($u) => str_contains($u, '/storage/media/clients/client-delsu.png'))
        ->where('home.clients.3.logoUrl', fn ($u) => str_contains($u, 'client-eduabroad.png'))
        ->where('home.clients.4.logoUrl', fn ($u) => str_contains($u, 'client-rccg.png'))
        ->has('home.roles', 8)
        ->where('home.sections.work.heading', 'Software that moves real money, students and shipments.')
        ->where('site.identity.name', 'Charles Aloaye Sedenu')
        ->where('site.contact.cv', route('cv'))
        ->where('site.primaryCta.label', 'Book a session or mentorship')
        ->where('site.primaryCta.href', '/contact')
        ->where('meta.title', 'Charles Aloaye - Software Engineer, Founder & Educator'));
});

it('serves real images for the portrait and every project, with no placeholder text', function () {
    $html = $this->get('/')->assertOk()->getContent();
    $home = $this->get('/')->viewData('page')['props']['home'];

    expect($home['hero']['portraitUrl'])->toContain('/storage/media/site/charlestechy1.jpg')
        ->and(collect($home['projects'])->pluck('screenshotUrl')->filter())->toHaveCount(5)
        ->and($html)->not->toContain('[Screenshot')->not->toContain('[Portrait')->not->toContain('[Client')->not->toContain('[--]');

    Storage::disk('public')->assertExists(['media/site/charlestechy1.jpg', 'media/projects/eduabroad.jpg', 'cv/charlesaloaye.pdf']);
});

it('renders the server-side title, description and share image for crawlers', function () {
    $this->get('/')->assertSee('<title inertia>Charles Aloaye - Software Engineer, Founder &amp; Educator</title>', false)
        ->assertSee('name="description" content="Charles Aloaye (CharlesTechy) is a Lagos-based software engineer', false)
        ->assertSee('property="og:image" content="'.url('/storage/media/site/charlestechy1.jpg').'"', false);
});

it('hides drafts and hidden projects, and shows testimonials only once published', function () {
    Project::factory()->status('draft')->create(['title' => 'Secret draft']);
    Testimonial::factory()->status('draft')->create(['quote' => 'Not yet']);

    $this->get('/')->assertInertia(fn (Assert $page) => $page
        ->where('home.projectCount', 10)
        ->has('home.testimonials', 0));

    Testimonial::factory()->create(['quote' => 'Charles rescued our launch.']);

    $this->get('/')->assertInertia(fn (Assert $page) => $page->where('home.testimonials.0.quote', 'Charles rescued our launch.'));
});

it('hides the availability badge when it is switched off', function () {
    SiteSettings::save('availability', ['enabled' => false]);

    $this->get('/')->assertInertia(fn (Assert $page) => $page
        ->where('site.availability', null)
        ->where('site.availabilityShort', null));
});

it('falls back to email for the main button when there is no WhatsApp link', function () {
    SiteSettings::save('hero', ['primaryUrl' => null]);
    SiteSettings::save('contact', ['whatsappUrl' => null]);
    SiteSettings::save('socials', [['label' => 'GitHub', 'href' => 'https://github.com/charlesaloaye']]);

    $this->get('/')->assertInertia(fn (Assert $page) => $page->where('site.primaryCta.href', 'mailto:hello@charlestechy.com'));
});

it('shows the 404 page in the site design for unknown URLs', function () {
    $this->get('/does-not-exist')->assertNotFound()->assertInertia(fn (Assert $page) => $page
        ->component('Error')
        ->where('status', 404)
        ->where('site.identity.name', 'Charles Aloaye Sedenu'));
});

it('sends security headers', function () {
    $this->get('/')->assertHeader('X-Content-Type-Options', 'nosniff')->assertHeader('X-Frame-Options', 'SAMEORIGIN');
});

it('never shows a phone number or WhatsApp anywhere on the public site', function () {
    foreach (['/', '/work', '/blog', '/blog/rescue-stalled-laravel-codebase', '/work/delsu-bursary', '/nope', '/feed.xml', '/sitemap.xml'] as $path) {
        expect($this->get($path)->getContent())->not->toContain('8137203803')->not->toContain('813 720')->not->toContain('wa.me')->not->toContain('/whatsapp');
    }

    $this->get('/')->assertInertia(fn (Assert $page) => $page
        ->where('site.contact.whatsapp', null)
        ->missing('site.contact.phone')
        ->where('site.socials', fn ($s) => collect($s)->pluck('label')->doesntContain('WhatsApp')));
    $this->get('/whatsapp')->assertNotFound();
    expect(json_encode(SiteSettings::all()))->not->toContain('8137203803');
});

it('404s the WhatsApp link when none is set', function () {
    SiteSettings::save('contact', ['whatsappUrl' => null]);
    SiteSettings::save('socials', []);

    $this->get('/whatsapp')->assertNotFound();
    $this->get('/')->assertInertia(fn (Assert $page) => $page->where('site.contact.whatsapp', null));
});

it('uses the logo image in the header and browser tab, and falls back to the letters', function () {
    $this->get('/')->assertInertia(fn (Assert $page) => $page
        ->where('site.identity.logoUrl', fn ($u) => str_contains($u, 'media/site/logo-charlesaloaye.jpg')))
        ->assertSee('rel="icon" href="', false);
    Storage::disk('public')->assertExists('media/site/logo-charlesaloaye.jpg');

    SiteSettings::save('identity', ['logoPath' => null]);
    $this->get('/')->assertInertia(fn (Assert $page) => $page->where('site.identity.logoUrl', null)->where('site.identity.mark', 'ct'))
        ->assertSee('href="/favicon.svg"', false);
});
