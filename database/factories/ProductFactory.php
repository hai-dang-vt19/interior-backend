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
            'description' => fake()->paragraph(),
            'price' => fake()->randomFloat(0, 100000, 10000000), // 100k - 10tr
            'discount_price' => fake()->optional(0.3)->randomFloat(0, 50000, 5000000), // 50k - 5tr
            'quantity' => fake()->numberBetween(0, 100),
            'image_url' => fake()->imageUrl(),
            'status' => fake()->randomElement([ProductStatus::ACTIVE->value, ProductStatus::INACTIVE->value, ProductStatus::OUT_OF_STOCK->value]),
        ];
    }
} 