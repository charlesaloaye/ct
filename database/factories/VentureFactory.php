<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\Venture> */
class VentureFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company(),
            'role' => 'CTO',
            'is_founder' => false,
            'status' => 'published',
        ];
    }
}
