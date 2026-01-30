<?php

namespace App\Http\Requests\Gedung;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGedung extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nama_gedung' => 'required|string|max:255|unique:gedung,nama_gedung,id,lokasi_id,',
            'tipe_gedung' => 'required|string|max:255',
            'lokasi_id' => 'required|exists:lokasi,id',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute harus diisi.',
            'string' => ':attribute harus berupa string.',
            'max' => ':attribute maksimal 255 karakter.',
            'unique' => ':attribute sudah terdaftar.',
            'exists' => ':attribute tidak ditemukan.',
        ];
    }
}
