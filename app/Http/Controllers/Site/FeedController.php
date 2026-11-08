<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Project;
use App\Support\PublicCache;
use App\Support\SiteSettings;
use Illuminate\Http\Response;

/** robots.txt and sitemap.xml for search engines, and an RSS feed of the blog. */
class FeedController extends Controller
{
    public function sitemap(): Response
    {
        $xml = PublicCache::remember('sitemap', function () {
            $articles = Article::query()->live()->orderByDesc('published_at')->get(['slug', 'updated_at']);
            $studies = Project::query()->forSite()->withCaseStudy()->get(['slug', 'updated_at']);

            $urls = [
                ['loc' => route('home'), 'lastmod' => null],
                ['loc' => route('about'), 'lastmod' => null],
                ['loc' => route('experience'), 'lastmod' => null],
                ['loc' => route('ventures'), 'lastmod' => null],
                ['loc' => route('products'), 'lastmod' => null],
                ['loc' => route('expertise'), 'lastmod' => null],
                ['loc' => route('media'), 'lastmod' => null],
                ['loc' => route('contact.page'), 'lastmod' => null],
                ['loc' => route('work.index'), 'lastmod' => null],
                ['loc' => route('blog.index'), 'lastmod' => $articles->max('updated_at')],
                ...$studies->map(fn ($p) => ['loc' => route('work.show', $p->slug), 'lastmod' => $p->updated_at]),
                ...$articles->map(fn ($a) => ['loc' => route('blog.show', $a->slug), 'lastmod' => $a->updated_at]),
            ];

            $body = collect($urls)->map(fn ($u) => '  <url><loc>'.e($u['loc']).'</loc>'
                .($u['lastmod'] ? '<lastmod>'.$u['lastmod']->toAtomString().'</lastmod>' : '').'</url>')->join("\n");

            return '<?xml version="1.0" encoding="UTF-8"?>'."\n"
                .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n{$body}\n</urlset>\n";
        });

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function robots(): Response
    {
        $lines = ['User-agent: *', 'Disallow: /admin', '', 'Sitemap: '.route('sitemap')];

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    public function rss(): Response
    {
        $xml = PublicCache::remember('rss', function () {
            $settings = SiteSettings::all();
            $title = $settings['seo']['blog']['title'] ?: ($settings['identity']['name'] ?: config('app.name'));
            $articles = Article::query()->with('category')->live()->orderByDesc('published_at')->limit(30)->get();

            $items = $articles->map(fn (Article $a) => '    <item>'
                .'<title>'.e($a->title).'</title>'
                .'<link>'.e(route('blog.show', $a->slug)).'</link>'
                .'<guid isPermaLink="true">'.e(route('blog.show', $a->slug)).'</guid>'
                .'<pubDate>'.$a->published_at->toRssString().'</pubDate>'
                .($a->category ? '<category>'.e($a->category->name).'</category>' : '')
                .'<description>'.e((string) $a->excerpt).'</description>'
                .'</item>')->join("\n");

            return '<?xml version="1.0" encoding="UTF-8"?>'."\n"
                .'<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom"><channel>'."\n"
                .'    <title>'.e($title).'</title>'."\n"
                .'    <link>'.e(route('blog.index')).'</link>'."\n"
                .'    <atom:link href="'.e(route('feed')).'" rel="self" type="application/rss+xml"/>'."\n"
                .'    <description>'.e((string) $settings['seo']['blog']['description']).'</description>'."\n"
                ."{$items}\n</channel></rss>\n";
        });

        return response($xml, 200, ['Content-Type' => 'application/rss+xml; charset=UTF-8']);
    }
}
