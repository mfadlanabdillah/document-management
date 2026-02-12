<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
            TagSeeder::class,
            AdminUserSeeder::class,
            UserSeeder::class,
            DocumentSeeder::class,
            UserManagementAuditLogSeeder::class,
        ]);
    }
}
