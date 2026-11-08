<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    public const UPDATED_AT = null;

    public const TYPES = ['page_view', 'cv_download', 'outbound_click'];

    protected $fillable = ['type', 'path', 'meta'];

    protected $casts = ['meta' => 'array', 'created_at' => 'datetime'];

    public static function record(string $type, ?string $path = null, ?array $meta = null): self
    {
        return static::query()->create(['type' => $type, 'path' => $path, 'meta' => $meta]);
    }
}
