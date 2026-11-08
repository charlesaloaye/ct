<?php

use App\Models\Project;
use App\Models\Testimonial;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(fn () => seedSite());

it('lists every visible project on /work, featured first', function () {
    Project::factory()->status('hidden')->create(['title' => 'Hidden one']);

    $this->get('/work')->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Work')
        ->has('projects', 10)
        ->where('projects', fn ($p) => collect($p)->pluck('slug')->contains('ecofiberx') && collect($p)->pluck('slug')->contains('esflogistics') && collect($p)->pluck('screenshotUrl')->filter()->count() === 10)
        ->where('projects.0.slug', 'delsu-bursary')
        ->where('sections.workPage.title', 'Projects I have built and shipped.')
        ->where('meta.title', 'Work - Charles Aloaye'));
});

it('serves a case study with the next one cycling round', function () {
    $this->get('/work/delsu-bursary')->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('CaseStudy')
        ->where('study.title', 'Digitising payroll for the Delta State University bursary')
        ->where('study.client', 'Delta State University')
        ->where('study.href', 'https://bursarydelsu.net')
        ->has('study.features', 4)
        ->where('study.next.slug', 'edu-abroad')
        ->where('meta.type', 'article'));

    $this->get('/work/wavecrest')->assertInertia(fn (Assert $page) => $page
        ->where('study.next.slug', 'delsu-bursary')
        ->has('study.gallery', 1));
});

it('keeps only real result figures from the seed data', function () {
    $this->get('/work/delsu-bursary')->assertInertia(fn (Assert $page) => $page
        ->has('study.results', 1)
        ->where('study.results.0.v', '2022'));
});

it('shows no live link for a project whose site is offline', function () {
    $this->get('/work/edu-abroad')->assertInertia(fn (Assert $page) => $page->where('study.href', null));
});

it('attaches a published testimonial to its project', function () {
    $project = Project::query()->where('slug', 'evergreen-updates')->sole();
    Testimonial::factory()->create(['project_id' => $project->id, 'quote' => 'Deliveries run themselves now.']);

    $this->get('/work/evergreen-updates')->assertInertia(fn (Assert $page) => $page->where('study.testimonial.quote', 'Deliveries run themselves now.'));
});

it('404s for unknown slugs, drafts and projects without a case study', function (string $slug) {
    $this->get("/work/{$slug}")->assertNotFound();
})->with(['nope', 'nysc-cds-benue', 'storyteller', 'greenfoot']);

it('has no "next" link when there is only one case study', function () {
    Project::query()->where('slug', '!=', 'delsu-bursary')->update(['has_case_study' => false]);

    $this->get('/work/delsu-bursary')->assertInertia(fn (Assert $page) => $page->where('study.next', null));
});
