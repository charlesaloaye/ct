<?php

namespace App\Content;

use App\Models\Article;
use App\Support\Status;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class ArticleType extends ContentType
{
    public function model(): string
    {
        return Article::class;
    }

    public function statuses(): array
    {
        return Status::ARTICLE;
    }

    protected function fields(): array
    {
        return [
            'title' => ['required', 'string', 'max:200'],
            'slug' => ['nullable', 'alpha_dash', 'max:200'],
            'categoryId' => ['nullable', 'integer', 'exists:article_categories,id'],
            'excerpt' => ['nullable', 'string', 'max:1000'],
            // A live article needs something to read.
            'body' => ['nullable', 'array', 'max:500', 'required_if:status,published,scheduled'],
            'body.*.type' => ['required', Rule::in(['p', 'h2', 'code', 'quote'])],
            'body.*.text' => ['required', 'string', 'max:20000'],
            'body.*.id' => ['nullable', 'alpha_dash', 'max:80'],
            'body.*.toc' => ['nullable', 'string', 'max:120'],
            'tags' => ['nullable', 'array', 'max:12'],
            'tags.*' => ['string', 'max:40'],
            'coverPath' => ['nullable', 'string', 'max:255'],
            'isFeatured' => ['boolean'],
            'publishedAt' => ['nullable', 'date', 'required_if:status,scheduled'],
            'seoTitle' => ['nullable', 'string', 'max:60'],
            'seoDescription' => ['nullable', 'string', 'max:155'],
            'notifySubscribers' => ['boolean'],
        ];
    }

    public function row(Model $item): array
    {
        return ['title' => $item->title, 'meta' => $item->category?->name];
    }

    public function searchColumns(): array
    {
        return ['title', 'excerpt'];
    }

    public function with(): array
    {
        return ['category'];
    }

    /** "Featured" in the admin filter means the featured flag, not a status. */
    public function applyStatusFilter(Builder $query, string $status): Builder
    {
        return $status === Status::FEATURED
            ? $query->where('is_featured', true)
            : $query->where('status', $status)->when($status === Status::PUBLISHED, fn ($q) => $q->where('is_featured', false));
    }

    public function statusLabel(Model $item): string
    {
        return $item->is_featured && $item->status === Status::PUBLISHED ? 'Featured' : Status::label($item->status);
    }

    public function extras(Model $item): array
    {
        return [
            'coverUrl' => $this->urlFor($item->cover_path),
            'category' => $item->category?->name,
            'live' => $item->isLive(),
        ];
    }
}
