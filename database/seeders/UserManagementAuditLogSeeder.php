<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserManagementAuditLog;
use Illuminate\Database\Seeder;

class UserManagementAuditLogSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (UserManagementAuditLog::query()->exists()) {
            return;
        }

        $admin = User::query()->where('role', 'admin')->first();
        $targets = User::query()->where('role', 'user')->take(5)->get();

        if (!$admin || $targets->isEmpty()) {
            return;
        }

        foreach ($targets as $index => $target) {
            UserManagementAuditLog::query()->create([
                'actor_user_id' => $admin->id,
                'target_user_id' => $target->id,
                'action' => $index % 2 === 0 ? 'user_created' : 'user_updated',
                'meta' => [
                    'source' => 'seeder',
                    'target_email' => $target->email,
                ],
            ]);
        }
    }
}
