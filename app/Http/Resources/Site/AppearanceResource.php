<?php

namespace App\Http\Resources\Site;

use App\Models\Appearance;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Appearance */
class AppearanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'outlet' => $this->outlet,
            'kind' => $this->kind,
            'href' => $this->url,
            'date' => $this->published_on?->toDateString(),
            'summary' => $this->summary,
        ];
    }
}
