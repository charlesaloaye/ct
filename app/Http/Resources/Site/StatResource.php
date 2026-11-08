<?php

namespace App\Http\Resources\Site;

use App\Models\Stat;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Stat */
class StatResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'v' => $this->value,
            'l' => $this->label,
            'short' => $this->short_label,
        ];
    }
}
