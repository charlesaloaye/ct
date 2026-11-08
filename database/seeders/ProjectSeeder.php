<?php

namespace Database\Seeders;

use App\Models\Project;
use Database\Seeders\Concerns\ReadsSeedData;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    use ReadsSeedData;

    /** Screenshots from the live charlestechy.com, by project slug. */
    private const SHOTS = [
        'delsu-bursary' => 'delta-state-university.jpg',
        'edu-abroad' => 'eduabroad.jpg',
        'evergreen-updates' => 'evergreen.jpg',
        'wavecrest' => 'wavecresthome.jpg',
        'storyteller' => 'storyteller.jpg',
        'greenfoot' => 'greenfoot.jpg',
        'ecommerce-management-system' => 'exclusive-world.jpg',
        'investment-management-system' => 'anglia-ruskin-university.jpg',
        'ecofiberx' => 'ecofiberx.jpg',
        'esflogistics' => 'esf.jpg',
        'nysc-cds-benue' => 'cdsbenue.jpg',
    ];

    /**
     * Live sites that no longer answer, so the site never shows a dead link.
     * eduabroad.ng stopped resolving (checked 1 Nov 2026); add the URL back in the admin if it returns.
     */
    private const OFFLINE = ['edu-abroad'];

    private const MORE = [
        'ecommerce-management-system' => [
            'title' => 'Ecommerce Management System',
            'tag' => 'Commerce',
            'description' => 'An online store for original products from Turkey - clothes, bags, shoes, belts and perfumes - with a storefront and pre-orders.',
        ],
        'investment-management-system' => [
            'title' => 'Investment Management System',
            'tag' => 'Fintech',
            'description' => 'A finance platform where investors sign up, secure their funds and manage their investments.',
        ],
        'ecofiberx' => [
            'title' => 'EcoFiberX',
            'tag' => 'Telecom',
            'description' => 'Website for a fibre internet provider serving homes and businesses in Nigeria, with plans, coverage, reseller and partner pages and customer sign-in.',
            'url' => 'https://ecofiberx.com',
            'domain' => 'ecofiberx.com',
        ],
        'greenfoot' => [
            'title' => 'Mobile Telecom Website',
            'tag' => 'Telecom',
            'description' => "Website for Greenfoot's SpeedOne360 internet service, with services, pricing, pre-orders and customer login.",
            'url' => 'https://greenfoot.com.ng',
            'domain' => 'greenfoot.com.ng',
        ],
        'esflogistics' => [
            'title' => 'EsfLogistics',
            'tag' => 'Logistics',
            'description' => 'Logistics website for shipping between the UK and Nigeria: sea and air freight services, a price list, parcel tracking and customer accounts.',
            'url' => 'https://esflogistics.com',
            'domain' => 'esflogistics.com',
        ],
    ];

    public function run(): void
    {
        $site = $this->data('site');
        $studies = $this->data('case-studies')['caseStudies'];

        // The featured card on the homepage.
        $this->create(1, 'delsu-bursary', [
            'title' => 'Delta State University bursary & payslip system',
            'tag' => 'Finance',
            'description' => 'The bursary ran payroll on paper and spreadsheets. I designed and built a web system that digitised payslips and financial operations for university staff - fewer manual errors, faster month-end.',
            'url' => 'https://bursarydelsu.net',
            'domain' => 'bursarydelsu.net',
            'status' => 'featured',
            'since' => '2022, ongoing',
            'tech_stack' => ['Laravel', 'MySQL', 'Bootstrap'],
        ], $studies['delsu-bursary']);

        $order = 2;
        foreach ($site['projects'] as $p) {
            $slug = $p['caseStudy'] ?? 'storyteller';
            $this->create($order++, $slug, [
                'title' => $p['title'],
                'tag' => $p['tag'],
                'description' => $p['desc'],
                'url' => $p['href'],
                'domain' => $p['domain'],
                'status' => 'published',
            ], $p['caseStudy'] ? $studies[$p['caseStudy']] : null);
        }

        // More real projects. Descriptions come from what each site itself says; no invented figures.
        foreach (self::MORE as $slug => $fields) {
            $this->create($order++, $slug, $fields + ['status' => 'published']);
        }

        // Kept as a draft until it gets a write-up.
        $this->create($order++, 'nysc-cds-benue', [
            'title' => 'NYSC CDS Benue website', 'tag' => 'Community', 'url' => 'https://cdsbenue.ng', 'domain' => 'cdsbenue.ng', 'status' => 'draft',
        ]);
    }

    private function create(int $order, string $slug, array $fields, ?array $study = null): void
    {
        $gallery = $slug === 'wavecrest' ? [$this->media('wavedashboard.jpg', 'Wavecrest investor dashboard', 'media/projects')] : [];

        $caseStudy = $study ? [
            'has_case_study' => true,
            'cs_category' => $study['cat'],
            'cs_title' => $study['title'],
            'cs_summary' => $study['summary'],
            'client' => $study['client'],
            'role' => $study['role'],
            'timeline' => $study['years'],
            'challenge' => $study['challenge'],
            'built' => $study['built'],
            'outcome' => $study['outcome'],
            'features' => $study['features'],
            // Only real figures: results still waiting for a number are left out until the admin adds them.
            'results' => array_values(array_filter($study['results'], fn ($r) => ! str_contains($r['v'], '['))),
            'gallery' => $gallery,
        ] : ['has_case_study' => false];

        $shot = self::SHOTS[$slug] ?? null;

        if (in_array($slug, self::OFFLINE, true)) {
            $fields['url'] = null;
            $fields['domain'] = null;
        }

        Project::query()->updateOrCreate(['slug' => $slug], [
            'sort_order' => $order,
            'show_on_mobile' => true,
            'screenshot_path' => $shot ? $this->media($shot, $fields['title'], 'media/projects') : null,
        ] + $fields + $caseStudy);
    }
}
