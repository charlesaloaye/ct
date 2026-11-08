<?php

namespace App\Http\Resources\Admin;

use App\Content\ContentType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

/** Rows for the admin table. (Not named *Collection so Laravel does not auto-wrap items.) */
class ContentRowList extends ResourceCollection
{
    public function __construct($resource, private readonly ContentType $type)
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        return $this->collection->map(fn ($item) => (new ContentRowResource($item, $this->type))->toArray($request))->all();
    }
}
