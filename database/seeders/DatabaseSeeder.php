<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Create admin user
        User::factory()->create([
            'username' => 'admin',
            'email' => 'admin@example.com',
            'role' => UserRole::ADMIN->value,
        ]);

        // Create staff users
        User::factory(5)->create([
            'role' => UserRole::STAFF->value,
        ]);

        // Create customers
        Customer::factory(10)->create();

        // Create categories
        $categories = Category::factory(5)->create();
        
        // Create subcategories
        $categories->each(function ($category) {
            Category::factory(3)->create([
                'parent_id' => $category->id,
            ]);
        });

        // Create products for each category
        Category::all()->each(function ($category) {
            Product::factory(5)->create([
                'category_id' => $category->id,
            ]);
        });
    }
}
