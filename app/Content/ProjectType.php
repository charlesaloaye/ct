<?php

namespace App\Content;

use App\Models\Project;
use Illuminate\Database\Eloquent\Model;

class ProjectType extends ContentType
{
    public function model(): string
    {
        return Project::class;
    }

    protected function fields(): array
    {
        $text = ['nullable', 'string', 'max:255'];

        return [
            'title' => ['required', 'string', 'max:160'],
            'slug' => ['nullable', 'alpha_dash', 'max:160'],
            'tag' => ['nullable', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:2000'],
            'url' => ['nullable', 'url:http,https', 'max:255'],
            'domain' => $text,
            'screenshotPath' => $text,
            'showOnMobile' => ['boolean'],
            'hasCaseStudy' => ['boolean'],
            'csCategory' => $text,
            'csTitle' => $text,
            'csSummary' => ['nullable', 'string', 'max:2000'],
            'client' => $text,
            'role' => $text,
            'timeline' => $text,
            'since' => $text,
            'challenge' => ['nullable', 'string', 'max:5000'],
            'built' => ['nullable', 'string', 'max:5000'],
            'outcome' => ['nullable', 'string', 'max:5000'],
            'features' => ['nullable', 'array', 'max:12'],
            'features.*.n' => ['nullable', 'string', 'max:8'],
            'features.*.t' => ['required', 'string', 'max:120'],
            'features.*.d' => ['nullable', 'string', 'max:500'],
            'results' => ['nullable', 'array', 'max:6'],
            'results.*.v' => ['required', 'string', 'max:40'],
            'results.*.l' => ['required', 'string', 'max:120'],
            'gallery' => ['nullable', 'array', 'max:20'],
            'gallery.*' => ['string', 'max:255'],
            'techStack' => ['nullable', 'array', 'max:30'],
            'techStack.*' => ['string', 'max:40'],
        ];
    }

    public function row(Model $item): array
    {
        return ['title' => $item->title, 'meta' => $item->domain];
    }

    public function searchColumns(): array
    {
        return ['title', 'domain', 'tag'];
    }

    public function extras(Model $item): array
    {
        return [
            'screenshotUrl' => $this->urlFor($item->screenshot_path),
            'galleryUrls' => collect($item->gallery ?? [])->map(fn ($p) => $this->urlFor($p))->values(),
        ];
    }
}
