<?php

namespace App\Http\Requests\Document;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization via Policy di controller
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                'string',
                'exists:document_statuses,code',
            ],
        ];
    }
}
