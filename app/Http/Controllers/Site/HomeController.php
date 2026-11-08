<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Resources\Site\HomeResource;
use App\Models\Client;
use App\Models\Experience;
use App\Models\ProcessStep;
use App\Models\Product;
use App\Models\Project;
use App\Models\RescueStep;
use App\Models\Service;
use App\Models\StackGroup;
use App\Models\Stat;
use App\Models\Testimonial;
use App\Support\Meta;
use App\Support\PublicCache;
use App\Support\Schema;
use App\Support\SiteSettings;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $home = PublicCache::remember('home', fn () => (new HomeResource([
            'settings' => SiteSettings::all(),
            'stats' => Stat::query()->ordered()->get(),
            'clients' => Client::query()->ordered()->get(),
            'rescue' => RescueStep::query()->ordered()->get(),
            'services' => Service::query()->visible()->ordered()->get(),
            'process' => ProcessStep::query()->ordered()->get(),
            'stack' => StackGroup::query()->ordered()->get(),
            'roles' => Experience::query()->visible()->ordered()->get(),
            'products' => Product::query()->visible()->ordered()->with('venture')->get(),
            'testimonials' => Testimonial::query()->visible()->ordered()->get(),
            'projects' => Project::query()->forSite()->get(),
        ]))->resolve($request));

        $seo = SiteSettings::group('seo')['home'];

        return Inertia::render('Home', [
            'home' => $home,
            'meta' => Meta::make($seo['title'], $seo['description'], suffix: false),
        ])->withViewData(['schema' => Schema::graph(Schema::website(), Schema::person())]);
    }
}
