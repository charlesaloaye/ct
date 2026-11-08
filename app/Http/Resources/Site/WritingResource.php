<?php

namespace App\Http\Resources\Site;

use App\Models\Writing;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Writing */
class WritingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'publication' => $this->publication,
            'href' => $this->url,
            'date' => $this->published_on?->toDateString(),
            'summary' => $this->summary,
        ];
    }
}
