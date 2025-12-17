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
            'nik.required'           => 'NIK harus diisi.',
            'nama.required'          => 'Nama harus diisi.',
            'email.required'         => 'Email harus diisi.',
            'email.email'            => 'Format email tidak valid.',
            'tgl_lahir.required'     => 'Tanggal lahir harus diisi.',
            'tempat_lahir.required'  => 'Tempat lahir harus diisi.',
            'no_tlp.required'        => 'Nomor telepon harus diisi.',
            'no_tlp.numeric'         => 'Nomor telepon harus angka.',
            'jenis_kelamin.required' => 'Jenis kelamin harus dipilih.',
            'status_kawin.required'  => 'Status kawin harus dipilih.',
            'agama.required'         => 'Agama harus dipilih.',
        ];
    }
}
