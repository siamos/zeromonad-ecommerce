<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->words(3, true);

        return [
            'name' => ['en' => ucfirst($name), 'el' => ucfirst($name)],
            'description' => ['en' => fake()->paragraph(), 'el' => fake()->paragraph()],
            'short_description' => ['en' => fake()->sentence(), 'el' => fake()->sentence()],
            'price' => fake()->randomFloat(2, 5, 500),
            'compare_price' => null,
            'stock' => fake()->numberBetween(1, 100),
            'sku' => strtoupper(fake()->bothify('SKU-####-??')),
            'status' => 'published',
            'featured' => false,
        ];
    }

    public function outOfStock(): static
    {
        return $this->state(fn () => ['stock' => 0]);
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => 'draft']);
    }
}
