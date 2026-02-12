<?php

use App\Models\Category;
use App\Models\DocumentStatus;
use App\Models\DocumentStatusTransition;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows admin to manage master data', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->postJson('/api/master/statuses', [
            'code' => 'review',
            'name' => 'Review',
            'description' => 'Review stage',
            'is_active' => true,
            'sort_order' => 4,
        ])
        ->assertCreated()
        ->assertJsonPath('code', 'review');

    $status = DocumentStatus::query()->where('code', 'review')->firstOrFail();

    $this->actingAs($admin)
        ->patchJson("/api/master/statuses/{$status->id}", [
            'name' => 'In Review',
        ])
        ->assertOk()
        ->assertJsonPath('name', 'In Review');

    $this->actingAs($admin)
        ->postJson('/api/master/categories', [
            'name' => 'Policy',
            'slug' => 'policy',
        ])
        ->assertCreated();

    $category = Category::query()->where('slug', 'policy')->firstOrFail();

    $this->actingAs($admin)
        ->postJson('/api/master/tags', [
            'name' => 'Security',
            'slug' => 'security',
        ])
        ->assertCreated();

    $tag = Tag::query()->where('slug', 'security')->firstOrFail();

    $this->actingAs($admin)
        ->deleteJson("/api/master/categories/{$category->id}")
        ->assertOk();

    $this->actingAs($admin)
        ->deleteJson("/api/master/tags/{$tag->id}")
        ->assertOk();

    $this->actingAs($admin)
        ->deleteJson("/api/master/statuses/{$status->id}")
        ->assertOk();
});

it('forbids non-admin to manage master data', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson('/api/master/categories', [
            'name' => 'Blocked',
            'slug' => 'blocked',
        ])
        ->assertForbidden();

    $this->actingAs($user)
        ->postJson('/api/master/tags', [
            'name' => 'blocked',
            'slug' => 'blocked',
        ])
        ->assertForbidden();

    $this->actingAs($user)
        ->postJson('/api/master/statuses', [
            'code' => 'blocked',
            'name' => 'Blocked',
        ])
        ->assertForbidden();
});

it('can update status transition matrix', function () {
    $admin = User::factory()->admin()->create();

    $draft = DocumentStatus::query()->where('code', 'draft')->firstOrFail();
    $active = DocumentStatus::query()->where('code', 'active')->firstOrFail();

    $this->actingAs($admin)
        ->patchJson("/api/master/statuses/{$draft->id}/transitions", [
            'to_status_ids' => [$active->id],
        ])
        ->assertOk();

    $this->assertDatabaseHas('document_status_transitions', [
        'from_status_id' => $draft->id,
        'to_status_id' => $active->id,
    ]);

    $this->actingAs($admin)
        ->patchJson("/api/master/statuses/{$draft->id}/transitions", [
            'to_status_ids' => [],
        ])
        ->assertOk();

    expect(
        DocumentStatusTransition::query()
            ->where('from_status_id', $draft->id)
            ->count()
    )->toBe(0);
});
