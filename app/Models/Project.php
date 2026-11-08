<?php

namespace App\Models;

use App\Models\Concerns\BustsPublicCache;
use App\Models\Concerns\HasContentStatus;
use App\Models\Concerns\HasSlug;
use App\Models\Concerns\Sortable;
use App\Support\Status;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use BustsPublicCache;
    use HasContentStatus;
    use HasFactory;
    use HasSlug;
    use Sortable;

    protected $fillable = [
        'title', 'slug', 'tag', 'description', 'domain', 'url', 'screenshot_path', 'status', 'sort_order',
        'show_on_mobile', 'has_case_study', 'cs_category', 'cs_title', 'cs_summary', 'client', 'role',
        'timeline', 'since', 'challenge', 'built', 'outcome', 'features', 'results', 'gallery', 'tech_stack',
    ];

    protected $casts = [
        'show_on_mobile' => 'boolean',
        'has_case_study' => 'boolean',
        'features' => 'array',
        'results' => 'array',
        'gallery' => 'array',
        'tech_stack' => 'array',
    ];

    protected static function booted(): void
    {
        // Keep the display domain in step with the URL when only the URL is given.
        static::saving(function (self $project) {
            if ($project->isDirty('url') && filled($project->url) && (blank($project->domain) || ! $project->isDirty('domain'))) {
                $project->domain = preg_replace('#^https?://(www\.)?#', '', rtrim($project->url, '/'));
            }
        });
    }

    /** Featured first, then by sort order. Drafts and hidden projects are excluded. */
    public function scopeForSite(Builder $query): Builder
    {
        return $query->visible()
            ->orderByRaw('CASE WHEN status = ? THEN 0 ELSE 1 END', [Status::FEATURED])
            ->ordered();
    }

    public function scopeWithCaseStudy(Builder $query): Builder
    {
        return $query->where('has_case_study', true);
    }

    public function testimonials(): HasMany
    {
        return $this->hasMany(Testimonial::class);
    }

    /** Projects have no published_at; only drafts may re-slug. */
    public function slugIsMutable(): bool
    {
        return ! $this->exists || $this->getOriginal('status') === Status::DRAFT;
    }
}
