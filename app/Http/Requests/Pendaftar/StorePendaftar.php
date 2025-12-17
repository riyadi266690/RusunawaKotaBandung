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
            'nama.required' => 'Nama harus diisi.',
            'nama.string' => 'Nama harus berupa string.',
            'nama.max' => 'Nama maksimal 255 karakter.',
            'telp_pendaftar.required' => 'Nomor Telepon harus diisi.',
            'telp_pendaftar.numeric' => 'Nomor Telepon harus berupa angka.',
            'suket.required' => 'Suket harus diisi.',
            'suket.file' => 'Suket harus berupa file.',
            'suket.mimes' => 'Suket harus berupa file PDF.',
            'suket.max' => 'Suket maksimal 2MB.',
        ];
    }
}
