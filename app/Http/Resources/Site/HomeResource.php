<?php

namespace App\Http\Resources\Site;

use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The homepage content: hero, section copy and every list, keyed like the
 * original src/data/site.js (stats, clients, projects, products, rescue,
 * services, process, stack, roles) plus testimonials.
 *
 * @property array $resource
 */
class HomeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $s = $this->resource['settings'];
        $hero = $s['hero'];
        $projects = $this->resource['projects'];

        return [
            'hero' => [
                'headline' => $hero['headline'],
                'intro' => $hero['intro'],
                'portraitUrl' => MediaUrl::for($hero['portraitPath']),
                'portraitAlt' => $hero['portraitAlt'] ?: $s['identity']['name'],
                'cvLabel' => $hero['cvLabel'],
                'shippedTitle' => $hero['shippedTitle'],
                'shippedNote' => $hero['shippedNote'],
                'shippedUrl' => $hero['shippedUrl'],
            ],
            'sections' => $s['sections'],
            'stats' => StatResource::collection($this->resource['stats'])->resolve($request),
            'clients' => $this->resource['clients']->map(fn ($c) => ['name' => $c->name, 'logoUrl' => MediaUrl::for($c->logo_path)])->values()->all(),
            // The homepage shows the featured project plus up to four more; /work lists them all.
            'projects' => ProjectCardResource::collection($projects->take(5))->resolve($request),
            'projectCount' => $projects->count(),
            'products' => ProductResource::collection($this->resource['products'])->resolve($request),
            'rescue' => StepResource::collection($this->resource['rescue'])->resolve($request),
            'services' => ServiceResource::collection($this->resource['services'])->resolve($request),
            'process' => StepResource::collection($this->resource['process'])->resolve($request),
            'stack' => StackGroupResource::collection($this->resource['stack'])->resolve($request),
            'roles' => ExperienceResource::collection($this->resource['roles'])->resolve($request),
            'testimonials' => TestimonialResource::collection($this->resource['testimonials'])->resolve($request),
        ];
    }
}
