<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\Appearance> */
class AppearanceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(5),
            'outlet' => fake()->company(),
            'kind' => 'interview',
            'url' => fake()->url(),
            'published_on' => now()->subMonth(),
            'status' => 'published',
        ];
    }
}
