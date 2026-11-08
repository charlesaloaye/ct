<?php

namespace App\Http\Resources\Site;

use App\Models\Product;
use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Product */
class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'mono' => $this->mono,
            'role' => $this->role,
            'name' => $this->name,
            'cat' => $this->category,
            'desc' => $this->description,
            'proof' => $this->proof,
            'domain' => $this->domain,
            'href' => $this->url,
            'logoUrl' => MediaUrl::for($this->logo_path),
            'venture' => $this->relationLoaded('venture') && $this->venture ? ['name' => $this->venture->name, 'slug' => $this->venture->slug] : null,
        ];
    }
}
