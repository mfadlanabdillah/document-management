<?php

use App\Models\User;
use App\Models\Document;
use App\Models\Category;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\DocumentActivityLog;

it('can list documents with pagination', function () {

    $user = User::factory()->create();
    $category = Category::factory()->create();

    Document::factory()
        ->count(15)
        ->create([
            'created_by' => $user->id,
            'category_id' => $category->id,
        ]);

    $this->actingAs($user);

    $response = $this->getJson('/api/documents');

    $response->assertOk()
        ->assertJsonStructure([
            'data',
            'links',
            'meta'
        ]);
});

it('can list documents', function () {

    $user = User::factory()->create();
    $category = Category::factory()->create();

    Document::factory()
        ->count(5)
        ->create([
            'created_by' => $user->id,
            'category_id' => $category->id,
        ]);

    $this->actingAs($user);

    $this->getJson('/api/documents')
        ->assertOk()
        ->assertJsonStructure([
            'data',
            'links',
            'meta'
        ]);
});

