<?php

namespace App\Support;

use App\Models\Setting;
use App\Models\StackGroup;
use App\Models\Venture;

/**
 * schema.org structured data (JSON-LD) so search engines can show rich
 * results: who Charles is, articles with dates and author, and breadcrumbs.
 * Rendered in the page <head> on the server; never sent as page props.
 */
final class Schema
{
    public static function personId(): string
    {
        return url('/').'#person';
    }

    public static function ventureId(string $slug): string
    {
        return route('ventures').'#'.$slug;
    }

    /**
     * The site owner, from Site settings: one stable @id used everywhere, so every
     * page describes the same entity.
     */
    public static function person(): array
    {
        $s = SiteSettings::all();
        $identity = $s['identity'];
        $ventures = Venture::query()->visible()->ordered()->get();

        return array_filter([
            '@type' => 'Person',
            '@id' => self::personId(),
            'name' => $identity['publicName'] ?: $identity['name'],
            'alternateName' => array_values(array_filter($identity['alternateNames'] ?? [])) ?: null,
            'url' => url('/'),
            'mainEntityOfPage' => route('about'),
            'image' => self::images($s),
            'jobTitle' => array_values(array_filter($identity['jobTitles'] ?? [])) ?: null,
            'description' => $s['about']['bioShort'] ?: $s['seo']['home']['description'],
            'email' => $s['contact']['email'] ? 'mailto:'.$s['contact']['email'] : null,
            'address' => filled($identity['location']) ? array_filter([
                '@type' => 'PostalAddress',
                'addressLocality' => trim(explode(',', $identity['location'])[0]),
                'addressCountry' => str_contains($identity['location'], 'Nigeria') ? 'NG' : null,
            ]) : null,
            'hasCredential' => self::credentials($identity),
            'worksFor' => $ventures->map(fn ($v) => ['@id' => self::ventureId($v->slug)])->values()->all() ?: null,
            'knowsAbout' => StackGroup::query()->ordered()->get()->flatMap(fn ($g) => $g->items)->unique()->values()->all() ?: null,
            'sameAs' => self::sameAs($s) ?: null,
        ]);
    }

    /** About page: Google's ProfilePage markup, with the Person as its main entity. */
    public static function profilePage(): array
    {
        $rows = Setting::query()->whereIn('key', ['identity', 'about', 'hero', 'socials'])->get(['created_at', 'updated_at']);

        return array_filter([
            '@type' => 'ProfilePage',
            '@id' => route('about').'#profile',
            'url' => route('about'),
            'name' => 'About '.(SiteSettings::value('identity.publicName') ?: SiteSettings::value('identity.name')),
            'dateCreated' => $rows->min('created_at')?->toIso8601String(),
            'dateModified' => $rows->max('updated_at')?->toIso8601String(),
            'isPartOf' => ['@id' => url('/').'#website'],
            'mainEntity' => self::person(),
        ]);
    }

    /** One Organization per visible venture, with Charles as founder or employee, and its products as brands. */
    public static function organizations(): array
    {
        return Venture::query()->visible()->ordered()->with('products')->get()->map(function (Venture $v) {
            $person = ['@id' => self::personId()];

            return array_filter([
                '@type' => 'Organization',
                '@id' => self::ventureId($v->slug),
                'name' => $v->name,
                'url' => $v->url,
                'logo' => MediaUrl::for($v->logo_path),
                'description' => $v->description,
                'founder' => $v->is_founder ? $person : null,
                'employee' => $v->is_founder ? null : $person,
                'brand' => $v->products->filter->isVisible()->map(fn ($p) => array_filter([
                    '@type' => 'Brand', 'name' => $p->name, 'url' => $p->url, 'logo' => MediaUrl::for($p->logo_path),
                ]))->values()->all() ?: null,
            ]);
        })->values()->all();
    }

    /** Profile photos in the aspect ratios Google asks for, where they exist. */
    private static function images(array $s): ?array
    {
        $images = ProfileImages::for($s['hero']['portraitPath'])
            ?: array_values(array_filter([MediaUrl::for($s['hero']['portraitPath'])]));

        return $images ?: null;
    }

    /** "TRCN-certified educator" in the job titles becomes a credential recognised by TRCN. */
    private static function credentials(array $identity): ?array
    {
        $titles = implode(' ', $identity['jobTitles'] ?? []).' '.($identity['authorTitle'] ?? '');
        if (! str_contains($titles, 'TRCN')) {
            return null;
        }

        return [[
            '@type' => 'EducationalOccupationalCredential',
            'name' => 'Registered teacher',
            'credentialCategory' => 'Professional certification',
            'recognizedBy' => [
                '@type' => 'Organization',
                'name' => 'Teachers Registration Council of Nigeria',
                'alternateName' => 'TRCN',
                'url' => 'https://trcn.gov.ng',
            ],
        ]];
    }

    /** Official profile URLs (not messaging links), from the social links in Site settings. */
    public static function sameAs(array $s): array
    {
        return collect($s['socials'])->pluck('href')
            ->filter(fn ($h) => str_starts_with((string) $h, 'http'))
            // A Messenger link points at the Facebook profile it opens.
            ->map(fn ($h) => preg_replace('#^https?://(www\.)?m\.me/#i', 'https://www.facebook.com/', $h))
            ->reject(fn ($h) => preg_match('#^https?://(wa\.me|api\.whatsapp\.com)/#i', $h))
            ->unique()->values()->all();
    }

    public static function website(): array
    {
        return [
            '@type' => 'WebSite',
            '@id' => url('/').'#website',
            'name' => SiteSettings::value('identity.publicName') ?: SiteSettings::value('identity.name') ?: config('app.name'),
            'alternateName' => SiteSettings::value('identity.wordmark') ?: null,
            'url' => url('/'),
            'publisher' => ['@id' => self::personId()],
        ];
    }

    /** @param  list<array{0: string, 1: string}>  $crumbs  [name, url] from the homepage down */
    public static function breadcrumbs(array $crumbs): array
    {
        return [
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($crumbs)->values()->map(fn ($c, $i) => [
                '@type' => 'ListItem', 'position' => $i + 1, 'name' => $c[0], 'item' => $c[1],
            ])->all(),
        ];
    }

    public static function article(array $post, string $url, ?string $image): array
    {
        $person = ['@id' => self::personId(), '@type' => 'Person', 'name' => SiteSettings::value('identity.publicName') ?: SiteSettings::value('identity.name'), 'url' => route('about')];

        return array_filter([
            '@type' => 'BlogPosting',
            'headline' => mb_substr($post['seoTitle'] ?: $post['title'], 0, 110),
            'description' => $post['seoDescription'] ?: $post['excerpt'],
            'image' => $image,
            'url' => $url,
            'mainEntityOfPage' => $url,
            'datePublished' => $post['publishedAt'],
            'dateModified' => $post['updatedAt'] ?? $post['publishedAt'],
            'author' => $person,
            'publisher' => $person,
            'articleSection' => $post['cat'],
            'keywords' => $post['tags'] ? implode(', ', $post['tags']) : null,
            'inLanguage' => str_replace('_', '-', config('app.locale')),
        ]);
    }

    public static function caseStudy(array $study, string $url): array
    {
        return array_filter([
            '@type' => 'CreativeWork',
            'name' => $study['title'],
            'description' => $study['summary'],
            'image' => $study['screenshotUrl'],
            'url' => $url,
            'creator' => ['@id' => self::personId()],
            'sourceOrganization' => $study['client'] ? ['@type' => 'Organization', 'name' => $study['client']] : null,
            'keywords' => $study['techStack'] ? implode(', ', $study['techStack']) : null,
        ]);
    }

    /** Wrap several nodes in one @graph document. */
    public static function graph(array ...$nodes): array
    {
        return ['@context' => 'https://schema.org', '@graph' => array_values($nodes)];
    }
}
