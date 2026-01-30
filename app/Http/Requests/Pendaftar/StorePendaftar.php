<?php

namespace App\Http\Requests\Pendaftar;

use Illuminate\Foundation\Http\FormRequest;

class StorePendaftar extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nama' => 'required|string|max:255',
            'telp_pendaftar' => 'required|numeric',
            'suket' => 'required|file|mimes:pdf|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute harus diisi.',
            'string' => ':attribute harus berupa teks.',
            'max' => ':attribute maksimal :max karakter.',
            'numeric' => ':attribute harus berupa angka.',
            'file' => ':attribute harus berupa file.',
            'mimes' => ':attribute harus berupa file PDF.',
        ];
    }
}
