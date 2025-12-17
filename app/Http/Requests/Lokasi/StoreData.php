<?php

namespace App\Http\Requests\DataLokasi;

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
        ];
    }

    public function messages()
    {
        return [
            'nama_lokasi.required' => 'Nama lokasi harus diisi.',
            'nama_lokasi.string' => 'Nama lokasi harus berupa string.',
            'nama_lokasi.max' => 'Nama lokasi maksimal 255 karakter.',
            'nama_lokasi.unique' => 'Nama lokasi sudah terdaftar.',
            'kepala_lokasi.required' => 'Kepala lokasi harus diisi.',
            'kepala_lokasi.string' => 'Kepala lokasi harus berupa string.',
            'kepala_lokasi.max' => 'Kepala lokasi maksimal 255 karakter.',
            'alamat_lokasi.required' => 'Alamat lokasi harus diisi.',
            'alamat_lokasi.string' => 'Alamat lokasi harus berupa string.',
            'alamat_lokasi.max' => 'Alamat lokasi maksimal 255 karakter.',
        ];
    }
}
