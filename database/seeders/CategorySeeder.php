<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::create([
            'name' => 'PRD',
            'slug' => 'prd',
            'description' => 'Product Requirement Document'
        ]);

        Category::create([
            'name' => 'BRD',
            'slug' => 'brd',
            'description' => 'Business Requirement Document'
        ]);

        Category::create([
            'name' => 'FSD',
            'slug' => 'fsd',
            'description' => 'Functional Specification Document'
        ]);

        Category::create([
            'name' => 'TSD',
            'slug' => 'tsd',
            'description' => 'Technical Specification Document'
        ]);

        Category::create([
            'name' => 'SOP',
            'slug' => 'sop',
            'description' => 'Standard Operating Procedure'
        ]);
    }
}
