<?php

namespace App\Http\Requests\Version;

use Illuminate\Foundation\Http\FormRequest;

class StoreVersionRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization via Policy di controller
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:5120', // 5MB
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
