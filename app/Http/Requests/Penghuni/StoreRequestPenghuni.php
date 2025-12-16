<?php

namespace App\Http\Requests\Penghuni;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequestPenghuni extends FormRequest
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
            'nik'           => 'required|unique:penghuni,nik',
            'email'         => 'required|email|unique:penghuni,email',
            'no_tlp'        => 'required|unique:penghuni,no_tlp',
            'nama'          => 'required|string',
            'tgl_lahir'     => 'required|date',
            'tempat_lahir'  => 'required',
            'jenis_kelamin' => 'required|in:1,2',
            'status_kawin'  => 'required',
            'agama'         => 'required',
            'pekerjaan'     => 'required',
            'alamat'        => 'required',
        ];
    }
}
