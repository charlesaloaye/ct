<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Resources\Site\AppearanceResource;
use App\Http\Resources\Site\ExperienceResource;
use App\Http\Resources\Site\ProductResource;
use App\Http\Resources\Site\ServiceResource;
use App\Http\Resources\Site\StackGroupResource;
use App\Http\Resources\Site\VentureResource;
use App\Models\Appearance;
use App\Models\Experience;
use App\Models\Product;
use App\Models\Service;
use App\Models\StackGroup;
use App\Models\Venture;
use App\Support\MediaUrl;
use App\Support\Meta;
use App\Support\PublicCache;
use App\Support\Schema;
use App\Support\SiteSettings;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/** The profile pages: About, Experience, Ventures, Products, Expertise, Media & press and Contact. */
class PageController extends Controller
{
    public function about(Request $request): Response
    {
        $s = SiteSettings::all();
        $data = PublicCache::remember('page:about', fn () => [
            'ventures' => VentureResource::collection(Venture::query()->visible()->ordered()->get())->resolve($request),
            'roles' => ExperienceResource::collection(Experience::query()->visible()->ordered()->limit(4)->get())->resolve($request),
        ]);

        return $this->page('About', 'about', $data + [
            'about' => $s['about'],
            'portraitUrl' => MediaUrl::for($s['hero']['portraitPath']),
            'portraitAlt' => $s['hero']['portraitAlt'] ?: $s['identity']['publicName'],
        ], fn () => [Schema::profilePage(), ...Schema::organizations(), Schema::breadcrumbs([['Home', route('home')], ['About', route('about')]])]);
    }

    public function experience(Request $request): Response
    {
        $data = PublicCache::remember('page:experience', fn () => [
            'roles' => ExperienceResource::collection(Experience::query()->visible()->ordered()->get())->resolve($request),
            'ventures' => VentureResource::collection(Venture::query()->visible()->ordered()->get())->resolve($request),
        ]);

        return $this->page('Experience', 'experience', $data);
    }

    public function ventures(Request $request): Response
    {
        $data = PublicCache::remember('page:ventures', fn () => [
            'ventures' => VentureResource::collection(Venture::query()->visible()->ordered()->with('products')->get())->resolve($request),
        ]);

        return $this->page('Ventures', 'ventures', $data, fn () => [...Schema::organizations(), Schema::breadcrumbs([['Home', route('home')], ['Ventures', route('ventures')]])]);
    }

    public function products(Request $request): Response
    {
        $data = PublicCache::remember('page:products', fn () => [
            'products' => ProductResource::collection(Product::query()->visible()->ordered()->with('venture')->get())->resolve($request),
        ]);

        return $this->page('Products', 'products', $data, fn () => [Schema::breadcrumbs([['Home', route('home')], ['Products', route('products')]])]);
    }

    public function expertise(Request $request): Response
    {
        $data = PublicCache::remember('page:expertise', fn () => [
            'stack' => StackGroupResource::collection(StackGroup::query()->ordered()->get())->resolve($request),
            'services' => ServiceResource::collection(Service::query()->visible()->ordered()->get())->resolve($request),
        ]);

        return $this->page('Expertise', 'expertise', $data);
    }

    public function media(Request $request): Response
    {
        $s = SiteSettings::all();
        $data = PublicCache::remember('page:media', fn () => [
            'appearances' => AppearanceResource::collection(Appearance::query()->visible()->orderByDesc('published_on')->ordered()->get())->resolve($request),
        ]);

        return $this->page('Media', 'media', $data + [
            'about' => $s['about'],
            'portraitUrl' => MediaUrl::for($s['hero']['portraitPath']),
            'logoUrl' => MediaUrl::for($s['identity']['logoPath']),
        ]);
    }

    public function contact(): Response
    {
        return $this->page('Contact', 'contact', ['contactCopy' => SiteSettings::group('sections')['contact']]);
    }

    /** Render a profile page with its copy, SEO and structured data. */
    private function page(string $component, string $key, array $props, ?\Closure $schema = null): Response
    {
        $seo = SiteSettings::group('seo')[$key];
        $copy = SiteSettings::group('sections')["{$key}Page"];
        $nodes = $schema ? $schema() : [Schema::breadcrumbs([['Home', route('home')], [$copy['eyebrow'] ?: ucfirst($key), route($key)]])];

        return Inertia::render($component, $props + [
            'copy' => $copy,
            'meta' => Meta::make($seo['title'], $seo['description']),
        ])->withViewData(['schema' => Schema::graph(...$nodes)]);
    }
}
