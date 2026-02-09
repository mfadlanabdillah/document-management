<?php

namespace App\Http\Requests\Document;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\DocumentStatus;

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
                Rule::in(array_column(DocumentStatus::cases(), 'value')),
            ],
        ];
    }
}
