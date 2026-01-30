<?php

namespace App\Http\Requests\Dokumen;

use Illuminate\Foundation\Http\FormRequest;

class UploadRevisiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file_revisi' => 'required|file|mimes:docx,pdf|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute harus diisi',
            'file' => ':attribute harus berupa file',
            'mimes' => ':attribute harus berupa file PDF/DOCX',
            'max' => ':attribute maksimal 10MB',
        ];
    }
}
