<?php

namespace App\Models;

use App\Models\Concerns\BustsPublicCache;
use App\Models\Concerns\Sortable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ArticleCategory extends Model
{
    use BustsPublicCache;
    use HasFactory;
    use Sortable;

    protected $fillable = ['name', 'slug', 'tint', 'sort_order'];

    protected static function booted(): void
    {
        static::saving(function (self $category) {
            $category->slug = $category->slug ?: Str::slug($category->name);
        });
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class, 'category_id');
    }
}
