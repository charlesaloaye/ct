<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        return [
            'title' => fake()->unique()->words(3, true),
            'tag' => 'Education',
            'description' => fake()->sentence(),
            'url' => 'https://'.fake()->domainName(),
            'status' => 'published',
            'show_on_mobile' => true,
        ];
    }

    public function status(string $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }

    public function withCaseStudy(): static
    {
        return $this->state(fn () => [
            'has_case_study' => true,
            'cs_category' => 'Education · Portal',
            'cs_title' => fake()->sentence(4),
            'cs_summary' => fake()->sentence(),
            'client' => fake()->company(),
            'role' => 'Lead developer',
            'timeline' => '2022 - present',
            'challenge' => fake()->paragraph(),
            'built' => fake()->paragraph(),
            'outcome' => fake()->paragraph(),
            'features' => [['n' => '01', 't' => 'Feature', 'd' => 'Does a thing.']],
            'results' => [['v' => '10x', 'l' => 'Faster']],
            'tech_stack' => ['Laravel'],
        ]);
    }
}
