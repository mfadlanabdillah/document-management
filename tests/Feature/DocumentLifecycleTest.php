<?php

use App\Models\User;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\DocumentActivityLog;

it('can create document with version 1', function () {

    Storage::fake('local');

    $user = User::factory()->create();
    $category = Category::factory()->create();

    $this->actingAs($user);

    $response = $this->postJson('/api/documents', [
        'title' => 'Test PRD',
        'category_id' => $category->id,
        'file' => UploadedFile::fake()->create('v1.pdf', 100, 'application/pdf'),
    ]);

    $response->assertCreated();

    $response->assertJsonPath('data.current_version.version_number', 1);
});

it('enforces status lifecycle correctly', function () {

    $user = User::factory()->create();
    $category = Category::factory()->create();

    $this->actingAs($user);

    // Create document first
    $docResponse = $this->postJson('/api/documents', [
        'title' => 'Lifecycle Test',
        'category_id' => $category->id,
        'file' => UploadedFile::fake()->create('v1.pdf', 100, 'application/pdf'),
    ]);

    $documentId = $docResponse->json('data.id');

    // Draft → Active
    $this->patchJson("/api/documents/$documentId", [
        'status' => 'active'
    ])->assertOk();

    // Active → Archived
    $this->patchJson("/api/documents/$documentId", [
        'status' => 'archived'
    ])->assertOk();

    // Archived → Active
    $this->patchJson("/api/documents/$documentId", [
        'status' => 'active'
    ])->assertOk();

    // Active → Draft (should fail)
    $this->patchJson("/api/documents/$documentId", [
        'status' => 'draft'
    ])->assertStatus(422);
});

it('cannot delete non-draft document', function () {

    $user = User::factory()->create();
    $category = Category::factory()->create();

    $this->actingAs($user);

    $docResponse = $this->postJson('/api/documents', [
        'title' => 'Delete Test',
        'category_id' => $category->id,
        'file' => UploadedFile::fake()->create('v1.pdf', 100, 'application/pdf'),
    ]);

    $documentId = $docResponse->json('data.id');

    // Make active
    $this->patchJson("/api/documents/$documentId", [
        'status' => 'active'
    ]);

    // Try delete
    $this->deleteJson("/api/documents/$documentId")
        ->assertForbidden();
});

it('logs activity when status changes', function () {

    $user = User::factory()->create();
    $category = Category::factory()->create();

    $this->actingAs($user);

    $response = $this->postJson('/api/documents', [
        'title' => 'Logging Test',
        'category_id' => $category->id,
        'file' => UploadedFile::fake()->create('v1.pdf', 100, 'application/pdf'),
    ]);

    $documentId = $response->json('data.id');

    // Clear previous logs (created log)
    DocumentActivityLog::query()->delete();

    // Draft → Active
    $this->patchJson("/api/documents/$documentId", [
        'status' => 'active'
    ])->assertOk();

    $this->assertDatabaseCount('document_activity_logs', 1);

    $this->assertDatabaseHas('document_activity_logs', [
        'document_id' => $documentId,
        'action' => 'status_changed',
        'performed_by' => $user->id,
    ]);

});

it('prevents other users from updating the document', function () {

    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $category = Category::factory()->create();

    $this->actingAs($owner);

    $response = $this->postJson('/api/documents', [
        'title' => 'Private Document',
        'category_id' => $category->id,
        'file' => UploadedFile::fake()->create('v1.pdf', 100, 'application/pdf'),
    ]);

    $documentId = $response->json('data.id');

    // Switch user
    $this->actingAs($otherUser);

    $this->patchJson("/api/documents/$documentId", [
        'status' => 'active'
    ])->assertForbidden();

});

it('returns tags in document detail response', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $category = Category::factory()->create();
    $tagA = Tag::query()->create(['name' => 'Tag A', 'slug' => 'tag-a']);
    $tagB = Tag::query()->create(['name' => 'Tag B', 'slug' => 'tag-b']);

    $this->actingAs($user);

    $createResponse = $this->postJson('/api/documents', [
        'title' => 'Tag Response Test',
        'category_id' => $category->id,
        'tag_ids' => [$tagA->id, $tagB->id],
        'file' => UploadedFile::fake()->create('v1.pdf', 100, 'application/pdf'),
    ])->assertCreated();

    $documentId = $createResponse->json('data.id');

    $detailResponse = $this->getJson("/api/documents/{$documentId}")
        ->assertOk();

    $tagIds = collect($detailResponse->json('data.tags'))->pluck('id')->all();

    expect($tagIds)->toContain($tagA->id, $tagB->id);
});
