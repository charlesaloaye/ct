<?php

namespace App\Models;

use App\Models\Concerns\BustsPublicCache;
use App\Models\Concerns\HasContentStatus;
use App\Models\Concerns\HasSlug;
use App\Models\Concerns\Sortable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** A company Charles founded or holds a leadership role in. */
class Venture extends Model
{
    use BustsPublicCache;
    use HasContentStatus;
    use HasFactory;
    use HasSlug;
    use Sortable;

    protected $fillable = ['name', 'slug', 'role', 'is_founder', 'description', 'url', 'logo_path', 'years', 'status', 'sort_order'];

    protected $casts = ['is_founder' => 'boolean'];

    /** HasSlug builds the slug from the title; a venture's title is its name. */
    public function getTitleAttribute(): ?string
    {
        return $this->name;
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class)->orderBy('sort_order');
    }

    /** Ventures have no publish date; only drafts may re-slug. */
    public function slugIsMutable(): bool
    {
        return ! $this->exists || $this->getOriginal('status') === 'draft';
    }
}
