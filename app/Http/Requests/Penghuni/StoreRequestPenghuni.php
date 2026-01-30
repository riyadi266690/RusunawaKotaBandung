<?php

namespace App\Http\Requests\Penghuni;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequestPenghuni extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nama'  => strtoupper($this->nama),
            'email' => strtolower($this->email),
        ]);
    }

    public function rules(): array
    {
        return [
            'nik'            => 'required|string|max:255',
            'nama'           => 'required|string|max:255',
            'email'          => 'required|email|max:255',
            'tgl_lahir'      => 'required|date',
            'tempat_lahir'   => 'required|string|max:255',
            'no_tlp'         => 'required|numeric',
            'jenis_kelamin'  => 'required|integer|in:1,2',
            'status_kawin'   => 'required|integer|in:1,2,3,4',
            'agama'          => 'required|integer|in:1,2,3,4,5,6,7',
            'pekerjaan'      => 'nullable|string|max:255',
            'alamat'         => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute harus diisi.',
            'string' => ':attribute harus berupa teks.',
            'max' => ':attribute maksimal :max karakter.',
            'numeric' => ':attribute harus berupa angka.',
            'date' => ':attribute harus berupa tanggal.',
            'email' => ':attribute harus berupa email.',
            'in' => ':attribute tidak valid.',
            'unique' => ':attribute sudah terdaftar.',
            'exists' => ':attribute tidak ditemukan.',
            'integer' => ':attribute harus berupa angka.',
        ];
    }
}
