<?php

namespace App\Http\Requests\Gedung;

use Illuminate\Foundation\Http\FormRequest;

class StoreGedung extends FormRequest
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
            'nama_gedung' => 'required|string|max:255|unique:gedung,nama_gedung,NULL,id,lokasi_id,',
            'tipe_gedung' => 'required|string|max:255',
            'lokasi_id' => 'required|exists:lokasi,id',
        ];
    }
}
