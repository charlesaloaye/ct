<?php

namespace App\Models;

use App\Jobs\SendArticleNewsletter;
use App\Models\Concerns\BustsPublicCache;
use App\Models\Concerns\HasSlug;
use App\Models\Concerns\Sortable;
use App\Support\Status;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Article extends Model
{
    use BustsPublicCache;
    use HasFactory;
    use HasSlug;
    use Sortable;

    public const WORDS_PER_MINUTE = 200;

    protected $fillable = [
        'title', 'slug', 'category_id', 'excerpt', 'body', 'tags', 'cover_path', 'is_featured', 'status',
        'read_minutes', 'published_at', 'seo_title', 'seo_description', 'notify_subscribers', 'sort_order',
    ];

    protected $casts = [
        'body' => 'array',
        'tags' => 'array',
        'is_featured' => 'boolean',
        'notify_subscribers' => 'boolean',
        'published_at' => 'datetime',
        'notified_at' => 'datetime',
        'read_minutes' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $article) {
            // Read time follows the body unless a value was set explicitly in this save.
            if ($article->isDirty('body') && ! $article->isDirty('read_minutes') && filled($article->body)) {
                $article->read_minutes = static::readMinutesFor($article->body);
            }

            $article->normalisePublishing();
        });

        static::saved(function (self $article) {
            if ($article->shouldNotifySubscribers()) {
                SendArticleNewsletter::dispatch($article->id);
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ArticleCategory::class, 'category_id');
    }

    /** Live on the public site: published and the publish date has passed. */
    public function scopeLive(Builder $query): Builder
    {
        return $query->where('status', Status::PUBLISHED)
            ->where(fn (Builder $q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    public function scopeForBlog(Builder $query): Builder
    {
        return $query->live()->orderByDesc('is_featured')->ordered()->orderByDesc('published_at');
    }

    public function isLive(): bool
    {
        return $this->status === Status::PUBLISHED && ($this->published_at === null || $this->published_at->isPast());
    }

    public function shouldNotifySubscribers(): bool
    {
        return $this->notify_subscribers && $this->notified_at === null && $this->isLive();
    }

    /**
     * "published" with a future date becomes "scheduled"; "scheduled" whose time has
     * come becomes "published"; publishing without a date stamps it now.
     */
    public function normalisePublishing(): void
    {
        if ($this->status === Status::PUBLISHED) {
            if ($this->published_at === null) {
                $this->published_at = now();
            } elseif ($this->published_at->isFuture()) {
                $this->status = Status::SCHEDULED;
            }
        } elseif ($this->status === Status::SCHEDULED && $this->published_at?->lte(now())) {
            $this->status = Status::PUBLISHED;
        }
    }

    /** @param  array<int, array{type?: string, text?: string}>  $body */
    public static function readMinutesFor(array $body): int
    {
        $words = collect($body)
            ->sum(fn ($block) => count(preg_split('/\s+/u', trim((string) ($block['text'] ?? '')), -1, PREG_SPLIT_NO_EMPTY)));

        return max(1, (int) ceil($words / self::WORDS_PER_MINUTE));
    }
}
