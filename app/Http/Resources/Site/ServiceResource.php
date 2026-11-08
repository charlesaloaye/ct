<?php

namespace App\Http\Resources\Site;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Service */
class ServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'n' => $this->number,
            'title' => $this->title,
            'desc' => $this->description,
            'featured' => $this->status === 'featured',
        ];
    }
}
