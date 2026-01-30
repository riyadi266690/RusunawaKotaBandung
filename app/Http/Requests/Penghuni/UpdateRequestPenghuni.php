<?php

namespace App\Http\Requests\Penghuni;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequestPenghuni extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
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
        $id = $this->route('id');
        return [
            'email' => [
                'required',
                'email',
                Rule::unique('penghuni', 'email')->ignore($id),
            ],
            'no_tlp' => [
                'required',
                Rule::unique('penghuni', 'no_tlp')->ignore($id),
            ],
            'nama' => 'required|string|max:255',
            'tgl_lahir' => 'required|date',
            'tempat_lahir' => 'required|string',
            'jenis_kelamin' => 'required|integer|in:1,2',
            'status_kawin' => 'required|integer|in:1,2,3,4',
            'agama' => 'required|integer|in:1,2,3,4,5,6,7',
            'pekerjaan' => 'nullable|string|max:255',
            'alamat' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute harus diisi.',
            'string' => ':attribute harus berupa teks.',
            'max' => ':attribute maksimal :max karakter.',
            'date' => ':attribute harus berupa tanggal.',
            'email' => ':attribute harus berupa email.',
            'unique' => ':attribute sudah terdaftar.',
            'in' => ':attribute tidak valid.',
            'integer' => ':attribute harus berupa angka.',
            'exists' => ':attribute tidak ditemukan.',
            'numeric' => ':attribute harus berupa angka.',
        ];
    }
}
