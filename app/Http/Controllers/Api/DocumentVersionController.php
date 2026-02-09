<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Version\StoreVersionRequest;
use App\Http\Resources\VersionResource;
use App\Models\Document;
use App\Models\DocumentVersion;
use App\Services\VersionService;
use Illuminate\Support\Facades\Storage;

class DocumentVersionController extends Controller
{
    public function __construct(
        protected VersionService $versionService
    ) {}

    /**
     * GET /documents/{document}/versions
     */
    public function index(Document $document)
    {
        $this->authorize('view', $document);

        $versions = $document->versions()
            ->orderByDesc('version_number')
            ->get();

        return VersionResource::collection($versions);
    }

    /**
     * POST /documents/{document}/versions
     */
    public function store(StoreVersionRequest $request, Document $document)
    {
        $this->authorize('update', $document);

        if ($document->trashed()) {
            abort(400, 'Cannot upload version to deleted document.');
        }

        $version = $this->versionService->upload(
            $document,
            $request->file('file'),
            $request->notes
        );

        return new VersionResource($version);
    }

    /**
     * GET /documents/{document}/versions/{version}/download
     */
    public function download(Document $document, DocumentVersion $version)
    {
        $this->authorize('view', $document);

        if ($version->document_id !== $document->id) {
            abort(404);
        }

        return Storage::download($version->file_path);
    }
}
