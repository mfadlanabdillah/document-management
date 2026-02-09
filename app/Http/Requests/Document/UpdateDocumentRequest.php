<?php

namespace App\Http\Requests\Document;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization via Policy di controller
        return true;
    }

    public function rules(): array
    {
        $documentId = $this->route('document')?->id;

        return [
            'title' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('documents')
                    ->where(fn ($query) =>
                        $query->where('category_id', $this->category_id ?? $this->document->category_id)
                              ->whereNull('deleted_at')
                    )
                    ->ignore($documentId),
            ],

            'category_id' => [
                'sometimes',
                'uuid',
                'exists:categories,id'
            ],

            'tag_ids' => [
                'nullable',
                'array'
            ],

            'tag_ids.*' => [
                'uuid',
                'exists:tags,id'
            ],
        ];
    }
}
