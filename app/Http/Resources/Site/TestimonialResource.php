<?php

namespace App\Http\Resources\Site;

use App\Models\Testimonial;
use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Testimonial */
class TestimonialResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quote' => $this->quote,
            'name' => $this->client_name,
            'role' => $this->client_role,
            'company' => $this->company,
            'avatarUrl' => MediaUrl::for($this->avatar_path),
            'projectId' => $this->project_id,
        ];
    }
}
