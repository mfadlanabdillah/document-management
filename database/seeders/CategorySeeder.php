<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'PRD',
                'slug' => 'prd',
                'description' => 'Product Requirement Document',
            ],
            [
                'name' => 'BRD',
                'slug' => 'brd',
                'description' => 'Business Requirement Document',
            ],
            [
                'name' => 'FSD',
                'slug' => 'fsd',
                'description' => 'Functional Specification Document',
            ],
            [
                'name' => 'TSD',
                'slug' => 'tsd',
                'description' => 'Technical Specification Document',
            ],
            [
                'name' => 'SOP',
                'slug' => 'sop',
                'description' => 'Standard Operating Procedure',
            ],
        ];

        foreach ($categories as $category) {
            Category::query()->updateOrCreate(
                ['slug' => $category['slug']],
                [
                    'name' => $category['name'],
                    'description' => $category['description'],
                ]
            );
        }
    }
}
