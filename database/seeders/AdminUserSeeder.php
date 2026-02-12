<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (User::where('role', 'admin')->exists()) {
            return;
        }

        $seedEmail = env('ADMIN_SEED_EMAIL', 'admin@example.com');
        $seedName = env('ADMIN_SEED_NAME', 'System Admin');
        $seedPassword = env('ADMIN_SEED_PASSWORD', 'password');

        $existingByEmail = User::where('email', $seedEmail)->first();
        if ($existingByEmail) {
            $existingByEmail->role = 'admin';
            $existingByEmail->save();
            return;
        }

        $firstUser = User::orderBy('created_at')->first();
        if ($firstUser) {
            $firstUser->role = 'admin';
            $firstUser->save();
            return;
        }

        User::create([
            'name' => $seedName,
            'email' => $seedEmail,
            'password' => Hash::make($seedPassword),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
    }
}
