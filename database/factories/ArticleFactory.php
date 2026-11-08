<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\ArticleCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    protected $model = Article::class;

    public function definition(): array
    {
        return [
            'title' => fake()->unique()->sentence(6),
            'category_id' => ArticleCategory::factory(),
            'excerpt' => fake()->sentence(),
            'body' => [['type' => 'p', 'text' => fake()->paragraph()]],
            'tags' => ['Laravel'],
            'status' => 'published',
            'published_at' => now()->subDay(),
        ];
    }

    public function status(string $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => 'draft', 'published_at' => null]);
    }
}
