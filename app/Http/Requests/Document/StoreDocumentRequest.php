<?php

namespace App\Http\Requests\Document;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Policy akan dicek di controller
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('documents')
                    ->where(fn ($query) =>
                        $query->where('category_id', $this->category_id)
                              ->whereNull('deleted_at')
                    ),
            ],

            'category_id' => [
                'required',
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

            'file' => [
                'nullable',
                'file',
                'max:5120',
                'mimes:pdf,docx,xlsx'
            ],

            'notes' => [
                'nullable',
                'string',
                'max:1000'
            ],
        ];
    }
}
