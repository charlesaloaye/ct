<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait Sortable
{
    public static function bootSortable(): void
    {
        static::creating(function ($model) {
            if ($model->sort_order === null) {
                $model->sort_order = (int) static::query()->max('sort_order') + 1;
            }
        });
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
