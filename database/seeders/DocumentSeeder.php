<?php

namespace Database\Seeders;

use App\Enums\DocumentStatus;
use App\Models\Category;
use App\Models\Document;
use App\Models\DocumentActivityLog;
use App\Models\DocumentVersion;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class DocumentSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $categories = Category::query()->get();
        $tags = Tag::query()->get();
        $users = User::query()->get();

        if ($categories->isEmpty() || $users->isEmpty()) {
            return;
        }

        for ($i = 1; $i <= 24; $i++) {
            $creator = $users[$i % $users->count()];
            $category = $categories[$i % $categories->count()];

            $document = Document::query()->create([
                'title' => sprintf('Seeded Document %02d - %s', $i, strtoupper($category->slug)),
                'category_id' => $category->id,
                'status' => DocumentStatus::DRAFT->value,
                'created_by' => $creator->id,
            ]);

            if ($tags->isNotEmpty()) {
                $document->tags()->sync(
                    $tags->shuffle()->take(min(3, $tags->count()))->pluck('id')->all()
                );
            }

            $latestVersion = null;
            $versionCount = $i % 3 === 0 ? 2 : 1;

            for ($versionNumber = 1; $versionNumber <= $versionCount; $versionNumber++) {
                $content = "Seeded file for {$document->title} version {$versionNumber}";
                $fileName = "v{$versionNumber}.txt";
                $filePath = "documents/{$document->id}/{$fileName}";

                Storage::disk('local')->put($filePath, $content);

                $latestVersion = DocumentVersion::query()->create([
                    'document_id' => $document->id,
                    'version_number' => $versionNumber,
                    'file_name' => $fileName,
                    'file_path' => $filePath,
                    'file_size' => strlen($content),
                    'mime_type' => 'text/plain',
                    'notes' => $versionNumber === 1 ? 'Initial seeded version' : 'Updated seeded version',
                    'uploaded_by' => $creator->id,
                ]);
            }

            if ($latestVersion) {
                $document->update(['current_version_id' => $latestVersion->id]);
            }

            DocumentActivityLog::query()->create([
                'document_id' => $document->id,
                'action' => 'created',
                'performed_by' => $creator->id,
                'meta' => ['source' => 'seeder'],
            ]);

            if ($i % 2 === 0) {
                $document->update(['status' => DocumentStatus::ACTIVE->value]);

                DocumentActivityLog::query()->create([
                    'document_id' => $document->id,
                    'action' => 'status_changed',
                    'performed_by' => $creator->id,
                    'meta' => [
                        'old_status' => DocumentStatus::DRAFT->value,
                        'new_status' => DocumentStatus::ACTIVE->value,
                    ],
                ]);
            }

            if ($i % 5 === 0 && $document->status === DocumentStatus::ACTIVE->value) {
                $document->update(['status' => DocumentStatus::ARCHIVED->value]);

                DocumentActivityLog::query()->create([
                    'document_id' => $document->id,
                    'action' => 'status_changed',
                    'performed_by' => $creator->id,
                    'meta' => [
                        'old_status' => DocumentStatus::ACTIVE->value,
                        'new_status' => DocumentStatus::ARCHIVED->value,
                    ],
                ]);
            }

            if ($i % 7 === 0 && $document->status === DocumentStatus::DRAFT->value) {
                $document->delete();

                DocumentActivityLog::query()->create([
                    'document_id' => $document->id,
                    'action' => 'deleted',
                    'performed_by' => $creator->id,
                    'meta' => ['source' => 'seeder'],
                ]);
            }
        }
    }
}
