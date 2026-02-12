<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Document;
use App\Models\DocumentStatus;
use App\Models\DocumentStatusTransition;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MasterDataController extends Controller
{
    public function statusIndex()
    {
        return Cache::remember('master:statuses:list', now()->addMinutes(30), function () {
            $statuses = DocumentStatus::query()
                ->with('allowedTransitions:id,code')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();

            return $statuses->map(function (DocumentStatus $status) {
                return [
                    'id' => $status->id,
                    'code' => $status->code,
                    'name' => $status->name,
                    'description' => $status->description,
                    'is_active' => $status->is_active,
                    'sort_order' => $status->sort_order,
                    'allowed_next_codes' => $status->allowedTransitions->pluck('code')->values(),
                    'created_at' => $status->created_at,
                    'updated_at' => $status->updated_at,
                ];
            });
        });
    }

    public function statusTransitionIndex()
    {
        return Cache::remember('master:status-transitions:list', now()->addMinutes(30), function () {
            return DocumentStatus::query()
                ->with('allowedTransitions:id,code,name')
                ->orderBy('sort_order')
                ->get()
                ->map(fn (DocumentStatus $status) => [
                    'from_status_id' => $status->id,
                    'from_status_code' => $status->code,
                    'to_status_ids' => $status->allowedTransitions->pluck('id')->values(),
                    'to_status_codes' => $status->allowedTransitions->pluck('code')->values(),
                ]);
        });
    }

    public function statusTransitionUpdate(Request $request, DocumentStatus $status)
    {
        $validated = $request->validate([
            'to_status_ids' => ['present', 'array'],
            'to_status_ids.*' => ['uuid', 'exists:document_statuses,id'],
        ]);

        $toIds = collect($validated['to_status_ids'])
            ->filter(fn (string $id) => $id !== $status->id)
            ->values();

        $activeTargetCount = DocumentStatus::query()
            ->whereIn('id', $toIds)
            ->where('is_active', true)
            ->count();

        if ($activeTargetCount !== $toIds->count()) {
            abort(422, 'Transitions can only target active statuses.');
        }

        DB::transaction(function () use ($status, $toIds) {
            DocumentStatusTransition::query()->where('from_status_id', $status->id)->delete();

            if ($toIds->isNotEmpty()) {
                $rows = $toIds->map(fn (string $toId) => [
                    'from_status_id' => $status->id,
                    'to_status_id' => $toId,
                ])->all();

                DB::table('document_status_transitions')->insert($rows);
            }
        });

        $this->invalidateStatusCaches();

        return response()->json(['success' => true]);
    }

    public function statusStore(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:document_statuses,code'],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ]);

        $validated['code'] = strtolower($validated['code']);

        $status = DocumentStatus::query()->create([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        $this->invalidateStatusCaches();

        return response()->json($status, 201);
    }

    public function statusUpdate(Request $request, DocumentStatus $status)
    {
        $validated = $request->validate([
            'code' => ['sometimes', 'string', 'max:50', 'alpha_dash', Rule::unique('document_statuses', 'code')->ignore($status->id)],
            'name' => ['sometimes', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ]);

        if (isset($validated['code'])) {
            $validated['code'] = strtolower($validated['code']);
        }

        if (($validated['is_active'] ?? null) === false) {
            $usedInDocument = Document::query()->where('status', $status->code)->exists();
            if ($usedInDocument) {
                abort(422, 'Cannot deactivate a status used by existing documents.');
            }
        }

        $status->update($validated);

        $this->invalidateStatusCaches();

        return $status->refresh();
    }

    public function statusDestroy(DocumentStatus $status)
    {
        if (in_array($status->code, ['draft', 'active', 'archived'], true)) {
            abort(422, 'System status cannot be deleted.');
        }

        $usedInDocument = Document::query()->where('status', $status->code)->exists();
        if ($usedInDocument) {
            abort(422, 'Cannot delete status used by existing documents.');
        }

        $hasTransitions = DocumentStatusTransition::query()
            ->where('from_status_id', $status->id)
            ->orWhere('to_status_id', $status->id)
            ->exists();

        if ($hasTransitions) {
            abort(422, 'Remove status transitions first before deleting this status.');
        }

        $status->delete();

        $this->invalidateStatusCaches();

        return response()->json(['success' => true]);
    }

    public function categoryIndex()
    {
        return Cache::remember('master:categories:full', now()->addMinutes(30), function () {
            return Category::query()->orderBy('name')->get();
        });
    }

    public function categoryStore(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:categories,name'],
            'slug' => ['nullable', 'string', 'max:120', 'unique:categories,slug'],
            'description' => ['nullable', 'string'],
        ]);

        $slug = $validated['slug'] ?? Str::slug($validated['name']);

        $category = Category::query()->create([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
        ]);

        $this->invalidateCategoryCaches();

        return response()->json($category, 201);
    }

    public function categoryUpdate(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:100', Rule::unique('categories', 'name')->ignore($category->id)],
            'slug' => ['sometimes', 'string', 'max:120', Rule::unique('categories', 'slug')->ignore($category->id)],
            'description' => ['nullable', 'string'],
        ]);

        $category->update($validated);

        $this->invalidateCategoryCaches();

        return $category->refresh();
    }

    public function categoryDestroy(Category $category)
    {
        $hasDocument = Document::query()->where('category_id', $category->id)->exists();
        if ($hasDocument) {
            abort(422, 'Cannot delete category used by existing documents.');
        }

        $category->delete();

        $this->invalidateCategoryCaches();

        return response()->json(['success' => true]);
    }

    public function tagIndex()
    {
        return Cache::remember('master:tags:full', now()->addMinutes(30), function () {
            return Tag::query()->orderBy('name')->get();
        });
    }

    public function tagStore(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:tags,name'],
            'slug' => ['nullable', 'string', 'max:120', 'unique:tags,slug'],
        ]);

        $slug = $validated['slug'] ?? Str::slug($validated['name']);

        $tag = Tag::query()->create([
            'name' => $validated['name'],
            'slug' => $slug,
        ]);

        $this->invalidateTagCaches();

        return response()->json($tag, 201);
    }

    public function tagUpdate(Request $request, Tag $tag)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:100', Rule::unique('tags', 'name')->ignore($tag->id)],
            'slug' => ['sometimes', 'string', 'max:120', Rule::unique('tags', 'slug')->ignore($tag->id)],
        ]);

        $tag->update($validated);

        $this->invalidateTagCaches();

        return $tag->refresh();
    }

    public function tagDestroy(Tag $tag)
    {
        $tag->documents()->detach();
        $tag->delete();

        $this->invalidateTagCaches();

        return response()->json(['success' => true]);
    }

    private function invalidateStatusCaches(): void
    {
        Cache::forget('master:statuses:list');
        Cache::forget('master:status-transitions:list');
    }

    private function invalidateCategoryCaches(): void
    {
        Cache::forget('master:categories:list');
        Cache::forget('master:categories:full');
    }

    private function invalidateTagCaches(): void
    {
        Cache::forget('master:tags:list');
        Cache::forget('master:tags:full');
    }
}
