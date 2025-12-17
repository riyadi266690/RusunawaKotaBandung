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
        return false;
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
            'email' => [
                'required',
                'email',
                Rule::unique('penghuni', 'email')->ignore($this->penghuni->id),
            ],
            'no_tlp' => [
                'required',
                Rule::unique('penghuni', 'no_tlp')->ignore($this->penghuni->id),
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
            'email.required' => 'Email harus diisi.',
            'email.unique' => 'Email sudah terdaftar.',
            'no_tlp.required' => 'Nomor telepon harus diisi.',
            'no_tlp.unique' => 'Nomor telepon sudah terdaftar.',
            'nama.required' => 'Nama harus diisi.',
            'nama.string' => 'Nama harus berupa string.',
            'nama.max' => 'Nama maksimal 255 karakter.',
            'tgl_lahir.required' => 'Tanggal lahir harus diisi.',
            'tgl_lahir.date' => 'Format tanggal tidak valid.',
            'tempat_lahir.required' => 'Tempat lahir harus diisi.',
            'tempat_lahir.string' => 'Tempat lahir harus berupa string.',
            'jenis_kelamin.required' => 'Jenis kelamin harus diisi.',
            'status_kawin.required' => 'Status kawin harus diisi.',
            'agama.required' => 'Agama harus diisi.',
            'pekerjaan.max' => 'Pekerjaan maksimal 255 karakter.',
            'alamat.max' => 'Alamat maksimal 255 karakter.',
        ];
    }
}
