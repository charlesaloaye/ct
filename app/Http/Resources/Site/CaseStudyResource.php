<?php

namespace App\Http\Resources\Site;

use App\Models\Project;
use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Project */
class CaseStudyResource extends JsonResource
{
    public function __construct($resource, private readonly ?array $next = null)
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        $testimonial = $this->relationLoaded('testimonials') ? $this->testimonials->first() : null;

        return [
            'slug' => $this->slug,
            'cat' => $this->cs_category,
            'title' => $this->cs_title ?: $this->title,
            'summary' => $this->cs_summary ?: $this->description,
            'client' => $this->client,
            'role' => $this->role,
            'years' => $this->timeline,
            'domain' => $this->domain,
            'href' => $this->url,
            'screenshotUrl' => MediaUrl::for($this->screenshot_path),
            'challenge' => $this->challenge,
            'built' => $this->built,
            'features' => $this->features ?? [],
            'outcome' => $this->outcome,
            'results' => $this->results ?? [],
            'gallery' => collect($this->gallery ?? [])->map(fn ($p) => MediaUrl::for($p))->filter()->values(),
            'techStack' => $this->tech_stack ?? [],
            'testimonial' => $testimonial ? (new TestimonialResource($testimonial))->resolve() : null,
            'next' => $this->next,
        ];
    }
}
