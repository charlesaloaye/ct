<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Project;
use App\Support\SiteSettings;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Checks every outbound link the site shows (projects, products, social links,
 * the "last shipped" card) and remembers the dead ones for the admin overview.
 * Only clear failures count: no DNS, no connection, 404/410 or a server error.
 * Sites that refuse robots (e.g. 400/403 from social networks) are not flagged.
 */
class CheckLinks extends Command
{
    public const CACHE_KEY = 'link-check';

    /** Sites that block automated checks outright, so "no answer" from them says nothing about the link. */
    private const BLOCKS_ROBOTS = ['linkedin.com'];

    protected $signature = 'site:check-links';

    protected $description = 'Check the outbound links shown on the site and flag dead ones in the admin';

    public function handle(): int
    {
        $links = $this->links();
        $this->info('Checking '.count($links).' links...');

        $responses = Http::pool(fn (Pool $pool) => collect($links)->map(fn ($link) => $pool->as($link['url'])
            ->withUserAgent('Mozilla/5.0 (compatible; '.parse_url(config('app.url'), PHP_URL_HOST).' link checker)')
            ->timeout(20)->connectTimeout(10)
            ->get($link['url']))->all());

        $broken = [];
        foreach ($links as $link) {
            $response = $responses[$link['url']] ?? null;
            $status = $response instanceof ConnectionException || $response === null ? null : $response->status();
            $host = preg_replace('/^www\./', '', (string) parse_url($link['url'], PHP_URL_HOST));
            $refusesRobots = collect(self::BLOCKS_ROBOTS)->contains(fn ($h) => $host === $h || str_ends_with($host, '.'.$h));
            $dead = ! $refusesRobots && ($status === null || in_array($status, [404, 410], true) || $status >= 500);

            $label = $refusesRobots ? '<fg=yellow>SKIP</>' : ($dead ? '<fg=red>DEAD</>' : '<fg=green>OK  </>');
            $this->line(sprintf('%s %s  (%s)', $label, $link['url'], $refusesRobots ? 'blocks automated checks' : ($status ?? 'no answer')));
            if ($dead) {
                $broken[] = $link + ['status' => $status];
            }
        }

        Cache::forever(self::CACHE_KEY, ['checkedAt' => now()->toIso8601String(), 'broken' => $broken]);

        return self::SUCCESS;
    }

    /** @return list<array{url: string, label: string, href: string}> where each link is shown and where to fix it */
    private function links(): array
    {
        $links = [];
        foreach (Project::query()->visible()->whereNotNull('url')->get() as $p) {
            $links[] = ['url' => $p->url, 'label' => "Project: {$p->title}", 'href' => route('admin.projects.edit', $p->id)];
        }
        foreach (Product::query()->visible()->whereNotNull('url')->get() as $p) {
            $links[] = ['url' => $p->url, 'label' => "Product: {$p->name}", 'href' => route('admin.products.edit', $p->id)];
        }
        foreach (SiteSettings::group('socials') as $s) {
            if (str_starts_with($s['href'] ?? '', 'http')) {
                $links[] = ['url' => $s['href'], 'label' => "Social link: {$s['label']}", 'href' => route('admin.settings.edit')];
            }
        }
        if (filled($shipped = SiteSettings::value('hero.shippedUrl'))) {
            $links[] = ['url' => $shipped, 'label' => '"Last shipped" card', 'href' => route('admin.settings.edit')];
        }

        return collect($links)->unique('url')->values()->all();
    }
}
