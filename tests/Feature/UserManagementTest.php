<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows admin to access user management endpoints', function () {
    $admin = User::factory()->admin()->create();
    $target = User::factory()->create();

    $this->actingAs($admin)
        ->getJson('/api/users')
        ->assertOk();

    $this->actingAs($admin)
        ->patchJson("/api/users/{$target->id}/role", ['role' => 'admin'])
        ->assertOk()
        ->assertJsonPath('data.role', 'admin');

    $this->assertDatabaseHas('user_management_audit_logs', [
        'action' => 'user_role_updated',
        'actor_user_id' => $admin->id,
        'target_user_id' => $target->id,
    ]);
});

it('forbids non-admin user management access', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();

    $this->actingAs($user)
        ->getJson('/api/users')
        ->assertForbidden();

    $this->actingAs($user)
        ->patchJson("/api/users/{$target->id}/role", ['role' => 'admin'])
        ->assertForbidden();
});
