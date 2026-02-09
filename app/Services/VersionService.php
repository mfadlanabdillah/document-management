<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentVersion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class VersionService
{
    public function upload(Document $document, $file, ?string $notes = null)
    {
        return DB::transaction(function () use ($document, $file, $notes) {

            $latestVersion = $document->versions()->max('version_number') ?? 0;
            $nextVersion = $latestVersion + 1;

            $fileName = 'v' . $nextVersion . '.' . $file->getClientOriginalExtension();
            $path = "documents/{$document->id}/{$fileName}";

            Storage::putFileAs(
                "documents/{$document->id}",
                $file,
                $fileName
            );

            $version = DocumentVersion::create([
                'document_id' => $document->id,
                'version_number' => $nextVersion,
                'file_name' => $fileName,
                'file_path' => $path,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'notes' => $notes,
                'uploaded_by' => Auth::id(),
            ]);

            $document->update([
                'current_version_id' => $version->id
            ]);

            return $version;
        });
    }
}
