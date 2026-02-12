<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $seedUsers = [
            [
                'name' => 'Product Owner',
                'email' => 'product.owner@example.com',
                'password' => 'password',
                'role' => 'user',
            ],
            [
                'name' => 'Project Manager',
                'email' => 'project.manager@example.com',
                'password' => 'password',
                'role' => 'user',
            ],
            [
                'name' => 'Business Analyst',
                'email' => 'business.analyst@example.com',
                'password' => 'password',
                'role' => 'user',
            ],
            [
                'name' => 'Quality Assurance',
                'email' => 'qa@example.com',
                'password' => 'password',
                'role' => 'user',
            ],
        ];

        foreach ($seedUsers as $data) {
            User::query()->updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make($data['password']),
                    'role' => $data['role'],
                    'email_verified_at' => now(),
                ]
            );
        }

        if (User::query()->count() < 10) {
            User::factory()->count(10 - User::query()->count())->create();
        }
    }
}
