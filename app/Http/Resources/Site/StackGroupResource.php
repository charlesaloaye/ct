<?php

namespace App\Http\Resources\Site;

use App\Models\StackGroup;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin StackGroup */
class StackGroupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'label' => $this->label,
            'items' => $this->items ?? [],
        ];
    }
}
