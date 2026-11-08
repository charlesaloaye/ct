<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Wraps an AI result: {body: blocks} | {text} | {seoTitle, seoDescription}. @property array $resource */
class AiResultResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return $this->resource;
    }
}
