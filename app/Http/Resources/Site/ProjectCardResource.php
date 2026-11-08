<?php

namespace App\Http\Resources\Site;

use App\Models\Project;
use App\Support\MediaUrl;
use App\Support\Status;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Project */
class ProjectCardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'tag' => $this->tag,
            'title' => $this->title,
            'desc' => $this->description,
            'domain' => $this->domain,
            'href' => $this->url,
            'screenshotUrl' => MediaUrl::for($this->screenshot_path),
            'caseStudy' => $this->has_case_study ? $this->slug : null,
            'featured' => $this->status === Status::FEATURED,
            'kicker' => $this->cs_category ?: $this->tag,
            'role' => $this->role,
            'since' => $this->since ?: $this->timeline,
            'showOnMobile' => $this->show_on_mobile,
        ];
    }
}
