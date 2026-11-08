<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Resources\Site\ArticleCategoryResource;
use App\Http\Resources\Site\ArticleResource;
use App\Http\Resources\Site\WritingResource;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Writing;
use App\Support\Meta;
use App\Support\PublicCache;
use App\Support\Schema;
use App\Support\SiteSettings;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BlogController extends Controller
{
    /** Notes for the ?subscription= state set by the confirm / unsubscribe email links. */
    public const SUBSCRIPTION_NOTES = [
        'confirmed' => "You're on the list - thanks!",
        'removed' => "You've been unsubscribed. Sorry to see you go.",
        'invalid' => 'That link has expired or was already used.',
    ];

    /** All live articles in one payload; search and category filters run instantly in the browser. */
    public function index(Request $request): Response
    {
        $blog = PublicCache::remember('blog', fn () => [
            'posts' => ArticleResource::collection(Article::query()->with('category')->forBlog()->get())->resolve($request),
            // Only categories that have something to read.
            // Articles published on Medium, DEV and Hashnode, newest first.
            'writings' => WritingResource::collection(Writing::query()->visible()->orderByDesc('published_on')->ordered()->get())->resolve($request),
            'categories' => ArticleCategoryResource::collection(
                ArticleCategory::query()->ordered()->whereHas('articles', fn (Builder $q) => $q->live())->get()
            )->resolve($request),
        ]);
        $seo = SiteSettings::group('seo')['blog'];

        return Inertia::render('Blog', [
            ...$blog,
            'sections' => SiteSettings::group('sections'),
            'subscriptionNote' => self::SUBSCRIPTION_NOTES[$request->query('subscription')] ?? null,
            'meta' => Meta::make($seo['title'], $seo['description']),
        ])->withViewData(['schema' => Schema::graph(Schema::breadcrumbs([['Home', route('home')], ['Blog', route('blog.index')]]))]);
    }

    public function show(Request $request, string $slug): Response
    {
        $payload = PublicCache::remember("article:{$slug}", function () use ($request, $slug) {
            $article = Article::query()->with('category')->live()->where('slug', $slug)->first();

            if (! $article) {
                return null;
            }

            $others = Article::query()->with('category')->live()->whereKeyNot($article->id)->orderByDesc('published_at');
            $sameCategory = (clone $others)->where('category_id', $article->category_id)->limit(3)->get();
            $related = $sameCategory->concat(
                (clone $others)->where(fn (Builder $q) => $q->whereNull('category_id')->orWhere('category_id', '!=', $article->category_id))
                    ->limit(3 - $sameCategory->count())->get()
            )->take(3);

            return [
                'post' => (new ArticleResource($article))->full()->resolve($request),
                'related' => ArticleResource::collection($related)->resolve($request),
            ];
        });

        abort_if($payload === null, 404);
        $post = $payload['post'];

        return Inertia::render('BlogPost', [
            ...$payload,
            'sections' => SiteSettings::group('sections'),
            'meta' => $meta = Meta::make($post['seoTitle'] ?: $post['title'], $post['seoDescription'] ?: $post['excerpt'], $post['coverUrl'], 'article'),
        ])->withViewData([
            'schema' => Schema::graph(
                Schema::article($post, route('blog.show', $slug), $meta['image']),
                Schema::breadcrumbs([['Home', route('home')], ['Blog', route('blog.index')], [$post['title'], route('blog.show', $slug)]]),
            ),
            'articleMeta' => ['published' => $post['publishedAt'], 'modified' => $post['updatedAt'] ?? null, 'section' => $post['cat'], 'tags' => $post['tags'] ?? []],
        ]);
    }
}
