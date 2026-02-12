<?php

use App\Models\Category;
use App\Models\Document;
use App\Models\Tag;
use App\Models\User;
use App\Models\UserManagementAuditLog;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('seeds all core domain data', function () {
    $this->seed(DatabaseSeeder::class);

    expect(Category::query()->count())->toBeGreaterThan(0);
    expect(Tag::query()->count())->toBeGreaterThan(0);
    expect(User::query()->count())->toBeGreaterThan(0);
    expect(User::query()->where('role', 'admin')->count())->toBeGreaterThan(0);
    expect(Document::query()->count())->toBeGreaterThan(0);
    expect(UserManagementAuditLog::query()->count())->toBeGreaterThan(0);
});
