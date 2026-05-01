<?php

namespace Database\Factories;

use App\Enums\ProductStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'category_id' => null, // Will be set in seeder
            'name' => fake()->unique()->words(3, true),
            'sku' => strtoupper(fake()->unique()->bothify('SP###??')),
            'description' => fake()->paragraph(),
            'description_short' => fake()->sentence(),
            'description_long' => fake()->optional()->paragraphs(2, true),
            'style' => fake()->optional()->randomElement(['Scandinavian', 'Industrial', 'Minimalist']),
            'space_type' => fake()->optional()->randomElement(['phong khach', 'phong ngu', 'van phong']),
            'origin' => fake()->optional()->randomElement(['Viet Nam', 'Y', 'Dan Mach']),
            'year_released' => fake()->optional()->numberBetween(2018, (int) date('Y')),
            'price' => fake()->randomFloat(0, 100000, 10000000), // 100k - 10tr
            'discount_price' => fake()->optional(0.3)->randomFloat(0, 50000, 5000000), // 50k - 5tr
            'quantity' => fake()->numberBetween(0, 100),
            'status' => fake()->randomElement([ProductStatus::ACTIVE->value, ProductStatus::INACTIVE->value, ProductStatus::OUT_OF_STOCK->value]),
            'is_active' => fake()->boolean(90),
            'is_customizable' => fake()->boolean(20),
        ];
    }
} 