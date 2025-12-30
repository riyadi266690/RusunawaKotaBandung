<?php

namespace App\Http\Requests\Dokumen;

use Illuminate\Foundation\Http\FormRequest;

class UploadRevisiRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'file_revisi' => 'required|file|mimes:docx,pdf|max:10240', // Max 10MB
        ];
    }
}