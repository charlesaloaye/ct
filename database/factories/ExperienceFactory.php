<?php

namespace Database\Factories;

use App\Models\Experience;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Experience>
 */
class ExperienceFactory extends Factory
{
    protected $model = Experience::class;

    public function definition(): array
    {
        return [
            'years' => '2020 - Now',
            'role' => 'Software Developer',
            'org' => fake()->unique()->company(),
            'place' => 'Lagos',
            'description' => fake()->sentence(),
            'status' => 'published',
        ];
    }

    public function status(string $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }
}
