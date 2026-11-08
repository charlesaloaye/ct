<?php

namespace App\Http\Resources\Site;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Process and rescue steps: {n, t, d}. */
class StepResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'n' => $this->number,
            't' => $this->title,
            'd' => $this->description,
        ];
    }
}
