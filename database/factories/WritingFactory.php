<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\Writing> */
class WritingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(6),
            'publication' => 'Medium',
            'url' => fake()->unique()->url(),
            'published_on' => now()->subMonths(2),
            'status' => 'published',
        ];
    }
}
