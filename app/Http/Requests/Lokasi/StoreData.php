<?php

namespace App\Http\Requests\Lokasi;

use Illuminate\Foundation\Http\FormRequest;

class StoreData extends FormRequest
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
            'nama_lokasi' => 'required|string|max:255|unique:lokasi,nama_lokasi',
            'kepala_lokasi' => 'required|string|max:255',
            'alamat_lokasi' => 'required|string|max:255',
            'mulai_dari' => 'required|string',
            'link_formulir' => 'nullable|string',
            'format_kontrak' => 'nullable|file|mime:doc,docx|max:2048',
        ];
    }

    public function messages()
    {
        return [
            'required' => ':attribute harus diisi.',
            'unique' => ':attribute sudah ada.',
            'max' => ':attribute tidak boleh lebih dari :max karakter.',
            'file' => ':attribute harus berupa file.',
            'mime' => ':attribute harus berupa file dengan ekstensi .doc atau .docx.',
            'string' => ':attribute harus berupa text.',
        ];
    }
}
