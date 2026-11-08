<?php

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Experience;
use App\Models\Product;
use App\Models\Project;
use App\Models\Service;
use App\Models\Testimonial;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(fn () => actingAsAdmin());

it('creates, lists, edits, updates and deletes every content type', function (string $type, string $model, array $input, string $column, string $meta) {
    $this->get("/admin/{$type}/create")->assertOk()->assertInertia(fn (Assert $page) => $page->component('admin/ContentEdit')->where('item', null));

    $this->post("/admin/{$type}", $input + ['status' => 'Published'])->assertRedirect();
    $item = $model::query()->sole();
    expect($item->status)->toBe('published');

    $this->get("/admin/{$type}")->assertInertia(fn (Assert $page) => $page
        ->component('admin/Collection')
        ->where('type', $type)
        ->where('rows.0.title', $item->{$column})
        ->where('rows.0.meta', $meta)
        ->where('rows.0.status', 'Published'));

    $this->get("/admin/{$type}/{$item->id}/edit")->assertInertia(fn (Assert $page) => $page->where('item.id', $item->id));

    $this->put("/admin/{$type}/{$item->id}", [Str::camel($column) => 'Renamed'])->assertRedirect();
    expect($item->fresh()->{$column})->toBe('Renamed');

    $this->delete("/admin/{$type}/{$item->id}")->assertRedirect("/admin/{$type}");
    expect($model::query()->count())->toBe(0);
})->with([
    'projects' => ['projects', Project::class, ['title' => 'Clinic booking', 'url' => 'https://clinic.example.com', 'tag' => 'Health'], 'title', 'clinic.example.com'],
    'products' => ['products', Product::class, ['name' => 'FrontStore', 'url' => 'https://frontstore.app', 'domain' => 'frontstore.app'], 'name', 'frontstore.app'],
    'services' => ['services', Service::class, ['title' => 'Web apps', 'number' => '01'], 'title', '01'],
    'experiences' => ['experiences', Experience::class, ['org' => 'Evergreen', 'role' => 'Developer', 'years' => '2024 - Now'], 'org', '2024 - Now'],
    // The testimonials table is titled by the quote, with the client after it.
    'testimonials' => ['testimonials', Testimonial::class, ['quote' => 'Great work.', 'clientName' => 'Ada', 'company' => 'Clinic Ltd'], 'quote', 'Ada, Clinic Ltd'],
]);

it('validates writes and keeps the input', function (string $type, array $input, string $field) {
    $this->from("/admin/{$type}/create")->post("/admin/{$type}", $input)->assertRedirect("/admin/{$type}/create")->assertSessionHasErrors($field);
})->with([
    ['projects', ['title' => ''], 'title'],
    ['projects', ['title' => 'X', 'url' => 'javascript:alert(1)'], 'url'],
    ['projects', ['title' => 'X', 'status' => 'Live'], 'status'],
    ['products', ['name' => 'X', 'url' => 'ftp://x'], 'url'],
    ['testimonials', ['quote' => 'Q', 'clientName' => 'A', 'projectId' => 999], 'projectId'],
    ['articles', ['title' => 'A live article needs a body', 'status' => 'published'], 'body'],
    ['articles', ['title' => 'X', 'status' => 'scheduled', 'body' => [['type' => 'p', 'text' => 'Hi']]], 'publishedAt'],
    ['articles', ['title' => 'X', 'body' => [['type' => 'script', 'text' => 'x']]], 'body.0.type'],
]);

it('saves a full case study and shows it on the public site', function () {
    $this->post('/admin/projects', [
        'title' => 'Clinic booking', 'url' => 'https://clinic.example.com', 'status' => 'Published', 'hasCaseStudy' => true,
        'csTitle' => 'Booking for a clinic network', 'csSummary' => 'Online booking.', 'client' => 'Clinic Ltd', 'timeline' => '2025',
        'challenge' => 'Phones rang all day.', 'built' => 'A booking system.', 'outcome' => 'Fewer calls.',
        'features' => [['n' => '01', 't' => 'Booking', 'd' => 'Pick a slot.']], 'results' => [['v' => '60%', 'l' => 'Fewer calls']],
        'techStack' => ['Laravel', 'React'],
    ])->assertRedirect();
    $project = Project::query()->sole();

    $this->get("/work/{$project->slug}")->assertOk()->assertInertia(fn (Assert $page) => $page
        ->where('study.title', 'Booking for a clinic network')
        ->where('study.results.0.v', '60%')
        ->where('study.techStack', ['Laravel', 'React']));
});

it('searches and filters by status', function () {
    Project::factory()->create(['title' => 'Alpha portal']);
    Project::factory()->status('draft')->create(['title' => 'Beta app']);

    $this->get('/admin/projects?q=alpha')->assertInertia(fn (Assert $page) => $page->has('rows', 1)->where('rows.0.title', 'Alpha portal')->where('total', 2));
    $this->get('/admin/projects?status=Draft')->assertInertia(fn (Assert $page) => $page->has('rows', 1)->where('rows.0.title', 'Beta app'));
});

it('reorders by a list of ids and the public site follows', function () {
    [$a, $b] = [Project::factory()->create(['sort_order' => 1]), Project::factory()->create(['sort_order' => 2])];

    $this->patch('/admin/projects/reorder', ['ids' => [$b->id, $a->id]])->assertRedirect();
    $this->get('/work')->assertInertia(fn (Assert $page) => $page->where('projects.0.id', $b->id)->where('projects.1.id', $a->id));

    $this->patch('/admin/projects/reorder', ['ids' => [999]])->assertSessionHasErrors('ids.0');
});

it('hides and restores the exact status an item had', function () {
    $project = Project::factory()->status('featured')->create();

    $this->patch("/admin/projects/{$project->id}/visibility")->assertRedirect();
    expect($project->fresh()->status)->toBe('hidden');
    $this->get('/work')->assertInertia(fn (Assert $page) => $page->has('projects', 0));

    $this->patch("/admin/projects/{$project->id}/visibility");
    expect($project->fresh()->status)->toBe('featured');
});

it('creates drafts, publishes, schedules and features articles', function () {
    $cat = ArticleCategory::factory()->create();
    // 399 words at 200 a minute = 2 minutes.
    $body = [['type' => 'h2', 'text' => 'Start', 'id' => 'start'], ['type' => 'p', 'text' => trim(str_repeat('word ', 398))]];

    $this->get('/admin/articles/create')->assertInertia(fn (Assert $page) => $page->component('admin/ArticleEdit')->has('options.categories', 1));

    $this->post('/admin/articles', ['title' => 'My first post', 'categoryId' => $cat->id, 'body' => $body, 'status' => 'draft'])->assertRedirect();
    $article = Article::query()->sole();
    expect($article->status)->toBe('draft')->and($article->read_minutes)->toBe(2);
    $this->get("/blog/{$article->slug}")->assertNotFound();

    $this->put("/admin/articles/{$article->id}", ['status' => 'published', 'isFeatured' => true])->assertRedirect();
    $this->get("/blog/{$article->slug}")->assertOk();
    $this->get('/admin/articles?status=Featured')->assertInertia(fn (Assert $page) => $page->has('rows', 1)->where('rows.0.status', 'Featured'));

    $this->put("/admin/articles/{$article->id}", ['status' => 'published', 'publishedAt' => now()->addDay()->toIso8601String()]);
    expect($article->fresh()->status)->toBe('scheduled');
    $this->get("/blog/{$article->slug}")->assertNotFound();
});

it('gives the editors what they need: categories, projects and existing tags', function () {
    Project::factory()->create(['tag' => 'Fintech', 'title' => 'Pay app']);

    $this->get('/admin/testimonials/create')->assertInertia(fn (Assert $page) => $page->where('options.projects.0.title', 'Pay app'));
    $this->get('/admin/projects/create')->assertInertia(fn (Assert $page) => $page->where('options.tags', ['Fintech'])->where('nextSortOrder', 2));
});

it('404s for unknown items', function () {
    $this->get('/admin/projects/999/edit')->assertNotFound();
    $this->put('/admin/projects/999', ['title' => 'x'])->assertNotFound();
});
