<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'description' => fake()->paragraph(),
            'sku' => fake()->unique()->regexify('[A-Z]{3}[0-9]{5}'),
            'price' => fake()->randomFloat(2, 10, 1000),
            'stock' => fake()->numberBetween(0, 500),
            'category' => fake()->randomElement(['Electronics', 'Clothing', 'Books', 'Home & Garden', 'Sports', 'Toys']),
            'status' => fake()->randomElement(['active', 'inactive', 'discontinued']),
            'is_featured' => fake()->boolean(20),
        ];
    }
}
