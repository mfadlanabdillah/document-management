<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Document\StoreDocumentRequest;
use App\Http\Requests\Document\UpdateDocumentRequest;
use App\Http\Requests\Document\UpdateStatusRequest;
use App\Http\Resources\DocumentResource;
use App\Http\Resources\DocumentActivityLogResource;
use App\Http\Resources\DocumentCollection;
use App\Models\Document;
use App\Services\DocumentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Search by title
        if ($request->filled('search')) {
            $query->where('title', 'ilike', '%' . $request->search . '%');
            // gunakan 'like' jika pakai sqlite/mysql
        }

        // Sorting
        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');

        $query->orderBy($sort, $direction);

        return DocumentResource::collection(
            $query->paginate(10)
        );
    }


    /**
     * POST /documents
     */
    public function store(StoreDocumentRequest $request): JsonResponse
    {
        $document = $this->documentService->create($request->validated());

        return (new DocumentResource(
            $document->load(['category', 'tags', 'currentVersion'])
        ))->response()->setStatusCode(201);
    }


    /**
     * GET /documents/{document}
     */
    public function show(Document $document): DocumentResource
    {
        $this->authorize('view', $document);

        return new DocumentResource(
            $document->load(['category', 'tags', 'versions'])
        );
    }

    /**
     * PUT /documents/{document}
     */
    public function update(UpdateDocumentRequest $request, Document $document): DocumentResource
    {
        $this->authorize('update', $document);

        $document->update($request->validated());

        if ($request->has('tag_ids')) {
            $document->tags()->sync($request->tag_ids);
        }

        return new DocumentResource(
            $document->load(['category', 'tags', 'currentVersion'])
        );
    }

    /**
     * PATCH /documents/{document}
     */
    public function updateStatus(UpdateStatusRequest $request, Document $document): DocumentResource
    {
        $this->authorize('update', $document);

        $document = $this->documentService->changeStatus(
            $document,
            $request->status
        );

        return new DocumentResource($document);
    }

    /**
     * DELETE /documents/{document}
     */
    public function destroy(Document $document): JsonResponse
    {
        $this->authorize('delete', $document);

        $this->documentService->delete($document);

        return response()->json([
            'success' => true,
            'message' => 'Document deleted successfully.',
            'data' => null,
            'meta' => [],
            'errors' => null,
        ]);
    }

    /**
     * POST /documents/{document}/restore
     */
    public function restore(Document $document): DocumentResource
    {
        $this->authorize('restore', $document);

        $document->restore();

        return new DocumentResource($document);
    }

    public function activities(Document $document)
    {
        $this->authorize('view', $document);

        $logs = $document->activityLogs()
            ->with('user')
            ->latest()
            ->get();

        return DocumentActivityLogResource::collection($logs);
    }
}
