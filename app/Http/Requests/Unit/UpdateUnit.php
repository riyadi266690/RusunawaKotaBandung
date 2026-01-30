<?php

namespace App\Http\Requests\Unit;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUnit extends FormRequest
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
            'gedung_id' => 'required|exists:gedung,id',
            'nomor' => [
                'required',
                'string',
                'max:255',
                Rule::unique('unit')->where(function ($query) {
                    return $query->where('gedung_id', $this->gedung_id)
                        ->where('lantai', $this->lantai)
                        ->where('tipe_unit', $this->tipe_unit);
                })
            ],
            'lantai' => 'required|integer|min:1|max:5',
            'tipe_unit' => 'required|string|in:Hunian,RBH',
            'status_jual' => 'required|string|in:0,1',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute harus diisi.',
            'string' => ':attribute harus berupa string.',
            'max' => ':attribute maksimal 255 karakter.',
            'unique' => ':attribute sudah terdaftar.',
            'min' => ':attribute minimal :min.',
            'integer' => ':attribute harus berupa angka.',
            'exists' => ':attribute tidak ditemukan.',
            'in' => ':attribute tidak valid.',
        ];
    }
}
