<?php

namespace App\Http\Resources\Admin;

use App\Content\ContentType;
use DateTimeInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

/**
 * Full record for the admin editors: every column as camelCase, plus a status
 * label and URLs for stored file paths.
 */
class ContentItemResource extends JsonResource
{
    public function __construct($resource, private readonly ContentType $type)
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        $fields = collect($this->resource->getAttributes())
            ->keys()
            ->mapWithKeys(function (string $column) {
                $value = $this->resource->getAttribute($column);

                return [Str::camel($column) => $value instanceof DateTimeInterface ? $value->format(DATE_ATOM) : $value];
            });

        return $fields->all() + [
            'statusLabel' => $this->type->statusLabel($this->resource),
        ] + $this->type->extras($this->resource);
    }
}
