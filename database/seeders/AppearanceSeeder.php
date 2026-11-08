<?php

namespace Database\Seeders;

use App\Models\Appearance;
use Illuminate\Database\Seeder;

/**
 * Talks and coverage Charles has confirmed. The DELSU Tech Invasion 1.0 date
 * is from the event's Eventbrite page (4 February 2023, 1000 Capacity Hall,
 * Abraka); the link is the organiser's (Hamplus Hub) event post.
 */
class AppearanceSeeder extends Seeder
{
    private const APPEARANCES = [
        [
            'title' => 'Speaker at DELSU Tech Invasion 1.0',
            'outlet' => 'DELSU Tech Invasion (Hamplus Hub)',
            'kind' => 'talk',
            'url' => 'https://blog.hamplustech.com/delsu_tech_invasion_1-0/',
            'published_on' => '2023-02-04',
            'summary' => 'Spoke at the first DELSU Tech Invasion, a tech conference for the Delta State University community in Abraka, organised by Hamplus Hub. The programme covered careers and earning in tech, and Python.',
        ],
    ];

    public function run(): void
    {
        foreach (self::APPEARANCES as $i => $row) {
            Appearance::query()->updateOrCreate(['url' => $row['url']], $row + ['status' => 'published', 'sort_order' => $i + 1]);
        }
    }
}
