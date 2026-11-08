<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Experience;
use App\Models\ProcessStep;
use App\Models\Product;
use App\Models\RescueStep;
use App\Models\Service;
use App\Models\StackGroup;
use App\Models\Stat;
use Database\Seeders\Concerns\ReadsSeedData;
use Illuminate\Database\Seeder;

class HomeContentSeeder extends Seeder
{
    use ReadsSeedData;

    private const LOGOS = [
        'Delta State University' => 'client-delsu.png',
        'Evergreen Updates' => 'client-evergreen.png',
        'Wavecrest' => 'client-wavecrest.png',
        'Edu Abroad' => 'client-eduabroad.png',
        'RCCG Faith Foundation' => 'client-rccg.png',
        'Hamplus Technologies' => 'client-hamplus.jpg',
        'EsfLogistics' => 'client-esflogistics.png',
        'MrHire' => 'client-mrhire.png',
    ];

    public function run(): void
    {
        $site = $this->data('site');

        foreach ($site['stats'] as $i => $s) {
            Stat::query()->create(['value' => $s['v'], 'label' => $s['l'], 'short_label' => $s['short'], 'sort_order' => $i + 1]);
        }

        foreach ($site['clients'] as $i => $name) {
            Client::query()->create([
                'name' => $name,
                // Real logos from each organisation's own site. Others show their name until one is uploaded.
                'logo_path' => isset(self::LOGOS[$name]) ? $this->media(self::LOGOS[$name], $name, 'media/clients') : null,
                'sort_order' => $i + 1,
            ]);
        }

        foreach ($site['products'] as $i => $p) {
            Product::query()->create([
                'mono' => $p['mono'], 'role' => $p['role'], 'name' => $p['name'], 'category' => $p['cat'],
                'description' => $p['desc'], 'proof' => $p['proof'], 'domain' => $p['domain'], 'url' => $p['href'],
                'status' => 'published', 'sort_order' => $i + 1,
            ]);
        }

        foreach ($site['rescue'] as $i => $r) {
            RescueStep::query()->create(['number' => $r['n'], 'title' => $r['t'], 'description' => $r['d'], 'sort_order' => $i + 1]);
        }

        foreach ($site['services'] as $i => $s) {
            Service::query()->create([
                'number' => $s['n'], 'title' => $s['title'], 'description' => $s['desc'],
                'status' => $s['title'] === 'Codebase rescue' ? 'featured' : 'published',
                'sort_order' => $i + 1,
            ]);
        }

        foreach ($site['process'] as $i => $p) {
            ProcessStep::query()->create(['number' => $p['n'], 'title' => $p['t'], 'description' => $p['d'], 'sort_order' => $i + 1]);
        }

        foreach ($site['stack'] as $i => $g) {
            StackGroup::query()->create(['label' => $g['label'], 'items' => $g['items'], 'sort_order' => $i + 1]);
        }

        foreach ($site['roles'] as $i => $r) {
            Experience::query()->create([
                'years' => $r['years'], 'role' => $r['role'], 'org' => $r['org'], 'place' => $r['place'],
                'description' => $r['desc'], 'status' => 'published', 'sort_order' => $i + 1,
            ]);
        }
    }
}
