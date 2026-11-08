<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Resources\Site\CaseStudyResource;
use App\Http\Resources\Site\ProjectCardResource;
use App\Models\Project;
use App\Support\Meta;
use App\Support\PublicCache;
use App\Support\Schema;
use App\Support\SiteSettings;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WorkController extends Controller
{
    /** Every visible project, featured first. */
    public function index(Request $request): Response
    {
        $projects = PublicCache::remember('work', fn () => ProjectCardResource::collection(Project::query()->forSite()->get())->resolve($request));
        $seo = SiteSettings::group('seo')['work'];

        return Inertia::render('Work', [
            'projects' => $projects,
            'sections' => SiteSettings::group('sections'),
            'meta' => Meta::make($seo['title'], $seo['description']),
        ])->withViewData(['schema' => Schema::graph(Schema::breadcrumbs([['Home', route('home')], ['Work', route('work.index')]]))]);
    }

    public function show(Request $request, string $slug): Response
    {
        $study = PublicCache::remember("case-study:{$slug}", function () use ($request, $slug) {
            $studies = Project::query()->forSite()->withCaseStudy()->get(['id', 'slug', 'title', 'cs_title', 'cs_category']);
            $index = $studies->search(fn (Project $p) => $p->slug === $slug);

            if ($index === false) {
                return null;
            }

            $project = Project::query()->with(['testimonials' => fn ($q) => $q->visible()->ordered()])
                ->findOrFail($studies[$index]->id);
            // With a single case study there is no "next" one to link to.
            $next = $studies->count() > 1 ? $studies[($index + 1) % $studies->count()] : null;

            return (new CaseStudyResource($project, $next ? [
                'slug' => $next->slug,
                'title' => $next->cs_title ?: $next->title,
                'cat' => $next->cs_category,
            ] : null))->resolve($request);
        });

        abort_if($study === null, 404);

        return Inertia::render('CaseStudy', [
            'study' => $study,
            'sections' => SiteSettings::group('sections'),
            'meta' => Meta::make($study['title'], $study['summary'], $study['screenshotUrl'], 'article'),
        ])->withViewData(['schema' => Schema::graph(
            Schema::caseStudy($study, route('work.show', $slug)),
            Schema::breadcrumbs([['Home', route('home')], ['Work', route('work.index')], [$study['title'], route('work.show', $slug)]]),
        )]);
    }
}
