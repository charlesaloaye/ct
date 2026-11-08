<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            SettingsSeeder::class,
            HomeContentSeeder::class,
            ProjectSeeder::class,
            VentureSeeder::class,
            WritingSeeder::class,
            AppearanceSeeder::class,
            BlogSeeder::class,
        ]);
    }
}
