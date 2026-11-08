<?php

namespace App\Http\Resources\Site;

use App\Models\Experience;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Experience */
class ExperienceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'years' => $this->years,
            'role' => $this->role,
            'org' => $this->org,
            'place' => $this->place,
            'desc' => $this->description,
        ];
    }
}
