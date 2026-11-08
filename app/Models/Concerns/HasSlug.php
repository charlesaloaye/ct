<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * Generates a unique slug from the title. Once the record has been published
 * the slug is frozen, so renaming a live article or project never breaks links.
 */
trait HasSlug
{
    public static function bootHasSlug(): void
    {
        static::saving(function ($model) {
            if (blank($model->slug) || ($model->isDirty('title') && ! $model->isDirty('slug') && $model->slugIsMutable())) {
                $model->slug = $model->uniqueSlug($model->title);
            } elseif ($model->isDirty('slug')) {
                $model->slug = $model->uniqueSlug($model->slug);
            }
        });
    }

    /** Draft records that have never gone live may change slug with their title. */
    public function slugIsMutable(): bool
    {
        if (! $this->exists) {
            return true;
        }

        return $this->getOriginal('status') === 'draft' && $this->getOriginal('published_at') === null;
    }

    public function uniqueSlug(?string $value): string
    {
        $base = Str::slug((string) $value) ?: Str::lower(class_basename($this));
        $slug = $base;
        $i = 2;

        while (static::query()->where('slug', $slug)->whereKeyNot($this->getKey())->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
