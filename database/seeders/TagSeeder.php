<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $tags = [
            ['name' => 'Urgent', 'slug' => 'urgent'],
            ['name' => 'Finance', 'slug' => 'finance'],
            ['name' => 'Legal', 'slug' => 'legal'],
            ['name' => 'Engineering', 'slug' => 'engineering'],
            ['name' => 'Product', 'slug' => 'product'],
            ['name' => 'HR', 'slug' => 'hr'],
            ['name' => 'Compliance', 'slug' => 'compliance'],
            ['name' => 'Operations', 'slug' => 'operations'],
        ];

        foreach ($tags as $tag) {
            Tag::query()->updateOrCreate(
                ['slug' => $tag['slug']],
                ['name' => $tag['name']]
            );
        }
    }
}
