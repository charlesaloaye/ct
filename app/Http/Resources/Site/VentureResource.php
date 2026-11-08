<?php

namespace App\Http\Resources\Site;

use App\Models\Venture;
use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Venture */
class VentureResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'role' => $this->role,
            'isFounder' => $this->is_founder,
            'desc' => $this->description,
            'href' => $this->url,
            'domain' => $this->url ? preg_replace('#^https?://(www\.)?#', '', rtrim($this->url, '/')) : null,
            'logoUrl' => MediaUrl::for($this->logo_path),
            'years' => $this->years,
            'products' => $this->relationLoaded('products')
                ? ProductResource::collection($this->products->filter(fn ($p) => $p->isVisible()))->resolve($request)
                : [],
        ];
    }
}
