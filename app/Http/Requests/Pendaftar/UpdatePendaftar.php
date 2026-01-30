<?php

namespace App\Http\Requests\Pendaftar;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePendaftar extends FormRequest
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
            'tgl_wawancara' => 'required|date',
            'tgl_final' => 'nullable|date',
            'ket_wawancara' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute harus diisi.',
            'date' => ':attribute harus berupa tanggal.',
            'string' => ':attribute harus berupa teks.',
        ];
    }
}
