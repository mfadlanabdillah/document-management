<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Document\StoreDocumentRequest;
use App\Http\Requests\Document\UpdateDocumentRequest;
use App\Http\Requests\Document\UpdateStatusRequest;
use App\Http\Resources\DocumentResource;
use App\Http\Resources\DocumentActivityLogResource;
use App\Models\Document;
use App\Services\DocumentService;
use Illuminate\Http\Request;
use App\Http\Resources\BaseApiResource;

class DocumentController extends Controller
{
    public function __construct(
        protected DocumentService $documentService
    ) {}

    /**
     * GET /documents
     */
    // public function index(Request $request): DocumentCollection
    // {
    //     $query = Document::with(['category', 'tags', 'currentVersion']);

    //     if ($request->filled('category_id')) {
    //         $query->where('category_id', $request->category_id);
    //     }

    //     if ($request->filled('status')) {
    //         $query->where('status', $request->status);
    //     }

    //     if ($request->filled('search')) {
    //         $query->where('title', 'ILIKE', "%{$request->search}%");
    //     }

    //     $documents = $query->paginate(10);

    //     return new DocumentCollection($documents);
    // }
    public function index(Request $request)
    {
        $query = Document::query()
            ->with(['category', 'currentVersion']);

        $status = $request->get('status');

        if ($status) {
            $query->where('status', $status);
        } else {
            $query->where('status', '!=', 'archived');
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $allowedSorts = ['created_at', 'updated_at', 'title'];

        $sort = $request->get('sort', 'created_at');
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'created_at';
        }

        $direction = $request->get('direction', 'desc');

        $query->orderBy($sort, $direction);

        return DocumentResource::collection(
            $query->paginate(10)
        );
    }

    /**
     * GET /documents/trash
     */
    public function trash(Request $request)
    {
        $query = Document::onlyTrashed()
            ->with(['category', 'currentVersion']);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->user()?->role !== 'admin') {
            $query->where('created_by', $request->user()->id);
        }

        $allowedSorts = ['deleted_at', 'created_at', 'updated_at', 'title'];
        $sort = $request->get('sort', 'deleted_at');
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'deleted_at';
        }

        $direction = $request->get('direction', 'desc');
        $query->orderBy($sort, $direction);

        return DocumentResource::collection(
            $query->paginate(10)
        );
    }

    /**
     * POST /documents
     */
    public function store(StoreDocumentRequest $request)
{
    $document = $this->documentService->create($request->validated());

    return BaseApiResource::success(
        new DocumentResource(
            $document->load(['category', 'tags', 'currentVersion'])
        ),
        'Document created successfully.',
        201
    );
}


    /**
     * GET /documents/{document}
     */
    public function show(Document $document)
    {
        $this->authorize('view', $document);

        return BaseApiResource::success(
            new DocumentResource(
                $document->load(['category', 'tags', 'versions'])
            ),
            'Document retrieved successfully.'
        );
    }

    /**
     * PUT /documents/{document}
     */
    public function update(UpdateDocumentRequest $request, Document $document)
    {
        $this->authorize('update', $document);

        $document->update($request->validated());

        if ($request->has('tag_ids')) {
            $document->tags()->sync($request->tag_ids);
        }

        return BaseApiResource::success(
            new DocumentResource(
                $document->load(['category', 'tags', 'currentVersion'])
            ),
            'Document updated successfully.'
        );
    }


    /**
     * PATCH /documents/{document}
     */
    public function updateStatus(UpdateStatusRequest $request, Document $document)
    {
        $this->authorize('update', $document);

        $document = $this->documentService->changeStatus(
            $document,
            $request->status
        );

        return BaseApiResource::success(
            new DocumentResource($document),
            'Status updated successfully.'
        );
    }


    /**
     * DELETE /documents/{document}
     */
    public function destroy(Document $document)
    {
        $this->authorize('delete', $document);

        $this->documentService->delete($document);

        return BaseApiResource::success(
            null,
            'Document deleted successfully.'
        );
    }


    /**
     * POST /documents/{document}/restore
     */
    public function restore(Document $document)
    {
        $this->authorize('restore', $document);

        $document->restore();

        return BaseApiResource::success(
            new DocumentResource($document),
            'Document restored successfully.'
        );
    }


    public function activities(Document $document)
    {
        $this->authorize('view', $document);

        $logs = $document->activityLogs()
            ->with('user')
            ->latest()
            ->get();

        return BaseApiResource::success(
            DocumentActivityLogResource::collection($logs),
            'Activities retrieved successfully.'
        );
    }

}
