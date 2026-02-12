<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentActivityLog;
use App\Models\DocumentStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DocumentService
{
    public function __construct(
        protected VersionService $versionService
    ) {}

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $document = Document::create([
                'title' => $data['title'],
                'category_id' => $data['category_id'],
                'status' => $this->defaultDraftStatusCode(),
                'created_by' => auth()->id(),
            ]);

            if (!empty($data['tag_ids'])) {
                $document->tags()->sync($data['tag_ids']);
            }

            if (request()->hasFile('file')) {
                $this->versionService->upload(
                    $document,
                    request()->file('file'),
                    $data['notes'] ?? null
                );
            }

            $this->log($document, 'created');

            return $document->fresh([
                'category',
                'tags',
                'currentVersion',
            ]);
        });
    }

    public function changeStatus(Document $document, string $newStatus)
    {
        $oldStatus = $document->status;

        if ($oldStatus === $newStatus) {
            throw ValidationException::withMessages([
                'status' => ['Status is already ' . $newStatus . '.'],
            ]);
        }

        $this->validateStatusTransition($oldStatus, $newStatus);

        $document->update([
            'status' => $newStatus,
        ]);

        $this->log($document, 'status_changed', [
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
        ]);

        return $document;
    }

    public function delete(Document $document)
    {
        if ($document->status !== 'draft') {
            throw new \Exception('Only draft documents can be deleted.');
        }

        return DB::transaction(function () use ($document) {
            $document->delete();

            $this->log($document, 'deleted');

            return true;
        });
    }

    protected function validateStatusTransition(string $from, string $to): void
    {
        $fromStatus = DocumentStatus::query()
            ->where('code', strtolower($from))
            ->where('is_active', true)
            ->first();

        $toStatus = DocumentStatus::query()
            ->where('code', strtolower($to))
            ->where('is_active', true)
            ->first();

        if (!$fromStatus || !$toStatus) {
            throw ValidationException::withMessages([
                'status' => ['Status does not exist or is inactive.'],
            ]);
        }

        $allowed = DB::table('document_status_transitions')
            ->where('from_status_id', $fromStatus->id)
            ->where('to_status_id', $toStatus->id)
            ->exists();

        if (!$allowed) {
            throw ValidationException::withMessages([
                'status' => ['Invalid status transition based on current lifecycle matrix.'],
            ]);
        }
    }

    private function log(Document $document, string $action, array $meta = [])
    {
        DocumentActivityLog::create([
            'document_id' => $document->id,
            'action' => $action,
            'performed_by' => Auth::id(),
            'meta' => $meta,
        ]);
    }

    private function defaultDraftStatusCode(): string
    {
        $draft = DocumentStatus::query()
            ->where('code', 'draft')
            ->where('is_active', true)
            ->first();

        if ($draft) {
            return $draft->code;
        }

        $firstActive = DocumentStatus::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->first();

        return $firstActive?->code ?? 'draft';
    }
}
