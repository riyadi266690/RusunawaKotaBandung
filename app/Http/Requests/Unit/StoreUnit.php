<?php

namespace App\Http\Requests\Unit;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class StoreUnit extends FormRequest
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

    public function messages()
    {
        return [
            'gedung_id.required' => 'Gedung harus dipilih.',
            'gedung_id.exists' => 'Gedung tidak valid.',
            'nomor.required' => 'Nomor unit harus diisi.',
            'nomor.unique' => 'Nomor unit sudah ada untuk gedung, lantai, dan tipe unit ini.', // Pesan error diperbarui
            'lantai.required' => 'Lantai harus diisi.',
            'lantai.integer' => 'Lantai harus berupa angka.',
            'lantai.min' => 'Lantai minimal 1.',
            'lantai.max' => 'Lantai maksimal 5.',
            'tipe_unit.required' => 'Tipe unit harus dipilih.',
            'tipe_unit.in' => 'Tipe unit tidak valid.',
            'status_jual.required' => 'Status jual harus dipilih.',
            'status_jual.in' => 'Status jual tidak valid.',
        ];
    }
}
