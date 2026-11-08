<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'mono' => 'Pr',
            'role' => 'Founder',
            'name' => fake()->unique()->company(),
            'category' => 'SaaS',
            'description' => fake()->sentence(),
            'proof' => 'Launched',
            'domain' => 'example.com',
            'url' => 'https://example.com',
            'status' => 'published',
        ];
    }

    public function status(string $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }
}
