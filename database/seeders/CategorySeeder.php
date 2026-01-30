<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Beverages',
                'description' => 'Soft drinks, juices, water, and other beverages',
                'is_active' => true
            ],
            [
                'name' => 'Snacks',
                'description' => 'Chips, crackers, nuts, and other snack items',
                'is_active' => true
            ],
            [
                'name' => 'Dairy Products',
                'description' => 'Milk, cheese, yogurt, and dairy items',
                'is_active' => true
            ],
            [
                'name' => 'Bakery',
                'description' => 'Bread, pastries, and baked goods',
                'is_active' => true
            ],
            [
                'name' => 'Personal Care',
                'description' => 'Toiletries, hygiene products, and personal care items',
                'is_active' => true
            ],
            [
                'name' => 'Household Items',
                'description' => 'Cleaning supplies and household necessities',
                'is_active' => true
            ]
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
