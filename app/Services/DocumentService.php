<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentActivityLog;
use App\Enums\DocumentStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Services\VersionService;
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
                'status' => DocumentStatus::DRAFT->value,
                'created_by' => auth()->id(),
            ]);

            if (!empty($data['tag_ids'])) {
                $document->tags()->sync($data['tag_ids']);
            }

            // 🔥 Upload initial version if file exists
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
                'currentVersion'
            ]);
        });
    }

    public function changeStatus(Document $document, string $newStatus)
    {
        $oldStatus = $document->status;

        if ($oldStatus === $newStatus) {
            throw ValidationException::withMessages([
                'status' => ['Status is already '.$newStatus.'.']
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
        if ($document->status !== DocumentStatus::DRAFT->value) {
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
        $from = strtolower($from);
        $to = strtolower($to);

        if ($from === $to) {
            return;
        }

        $allowedTransitions = [
            'draft' => ['active'],
            'active' => ['archived'],
            'archived' => ['active'],
        ];

        if (!isset($allowedTransitions[$from]) 
            || !in_array($to, $allowedTransitions[$from])) {

            throw ValidationException::withMessages([
                'status' => ['Invalid status transition.']
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
}
