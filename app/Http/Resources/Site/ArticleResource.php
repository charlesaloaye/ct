<?php

namespace App\Http\Resources\Site;

use App\Models\Article;
use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Matches the post shape in src/data/blog.js: {slug, featured, cat, read, title, excerpt, tags, body}.
 * The body is only included on the single-article endpoint.
 *
 * @mixin Article
 */
class ArticleResource extends JsonResource
{
    public bool $withBody = false;

    public function full(): static
    {
        $this->withBody = true;

        return $this;
    }

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'featured' => $this->is_featured,
            'cat' => $this->category?->name,
            'catSlug' => $this->category?->slug,
            'tint' => $this->category?->tint,
            'read' => $this->read_minutes.' min read',
            'title' => $this->title,
            'excerpt' => $this->excerpt,
            'tags' => $this->tags ?: null,
            'coverUrl' => MediaUrl::for($this->cover_path),
            'publishedAt' => $this->published_at?->toIso8601String(),
            $this->mergeWhen($this->withBody, fn () => [
                'body' => $this->body ?: null,
                'seoTitle' => $this->seo_title,
                'seoDescription' => $this->seo_description,
                'updatedAt' => $this->updated_at?->toIso8601String(),
            ]),
        ];
    }
}
