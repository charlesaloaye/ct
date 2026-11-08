<?php

namespace App\Http\Resources\Admin;

use App\Content\ContentType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** The row shape the admin Collection table uses: {id, title, meta, status}. */
class ContentRowResource extends JsonResource
{
    public function __construct($resource, private readonly ContentType $type)
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        return ['id' => $this->id] + $this->type->row($this->resource) + [
            'status' => $this->type->statusLabel($this->resource),
            'sortOrder' => $this->sort_order,
        ];
    }
}
