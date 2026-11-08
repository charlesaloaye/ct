<?php

use App\Models\Article;
use App\Models\Media;
use App\Models\Project;
use App\Models\Testimonial;
use App\Models\User;
use App\Support\SiteSettings;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Storage;

it('seeds the admin from env credentials and the real site content', function () {
    seedSite();

    $admin = User::query()->sole();
    expect($admin->is_admin)->toBeTrue()
        ->and($admin->email)->toBe('owner@example.com')
        ->and(Hash::check('a-strong-password-123', $admin->password))->toBeTrue()
        ->and(Article::query()->count())->toBe(18)
        ->and(Project::query()->withCaseStudy()->pluck('slug')->sort()->values()->all())
        ->toBe(['delsu-bursary', 'edu-abroad', 'evergreen-updates', 'wavecrest']);
});

it('seeds no placeholder content', function () {
    seedSite();

    $everything = json_encode([
        SiteSettings::all(),
        Project::query()->get()->toArray(),
        Article::query()->get(['title', 'excerpt', 'body'])->toArray(),
    ]);

    expect($everything)->not->toContain('[--]')->not->toContain('[Client')->not->toContain('[Screenshot')->not->toContain('lorem')
        ->and(Testimonial::query()->count())->toBe(0)
        ->and(Article::query()->where('status', 'published')->whereNull('body')->count())->toBe(0);
});

it('copies the real images and CV into storage and the media library', function () {
    seedSite();

    $paths = Project::query()->whereNotNull('screenshot_path')->pluck('screenshot_path')
        ->push(SiteSettings::value('hero.portraitPath'), SiteSettings::value('contact.cvPath'));

    expect($paths->count())->toBe(13)
        ->and(Media::query()->count())->toBeGreaterThanOrEqual(10);
    $paths->each(fn ($p) => Storage::disk('public')->assertExists($p));
});

it('refuses to seed without admin credentials', function () {
    Storage::fake('public');
    config(['site.admin.email' => null, 'site.admin.password' => null]);

    expect(fn () => $this->seed(DatabaseSeeder::class))->toThrow(RuntimeException::class, 'ADMIN_EMAIL');
});
