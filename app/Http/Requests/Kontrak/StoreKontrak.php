<?php

namespace App\Http\Requests\Kontrak;

use App\Models\Kontrak;
use Illuminate\Foundation\Http\FormRequest;

class StoreKontrak extends FormRequest
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
            'unit_id' => 'required|exists:unit,id|unique:kontrak,unit_id,NULL,id,status_kontrak,1',
            'no_kontrak' => 'required|string|max:255|unique:kontrak,no_kontrak',
            'tipe_kontrak' => 'required|integer|in:1,2',
            'harga_sewa' => 'required|integer',
            'harga_air' => 'nullable|integer',
            'jenis_usaha' => 'nullable|string|max:255',
            'luas_usaha' => 'nullable|numeric',
            'tgl_awal' => 'required|date',
            'tgl_akhir' => 'required|date|after_or_equal:tgl_awal',
            'nama_pihak1' => 'required|string|max:255',
            'status_ttd' => 'required|integer|in:0,1',
            'penghuni_1' => [
                'required',
                'exists:penghuni,id',
                function ($attribute, $value, $fail) {
                    $existing = Kontrak::where('status_kontrak', 1)
                        ->where('tipe_kontrak', $this->tipe_kontrak)
                        ->where(function ($query) use ($value) {
                            $query->where('penghuni_1', $value)
                                ->orWhere('penghuni_2', $value)
                                ->orWhere('penghuni_3', $value)
                                ->orWhere('penghuni_4', $value);
                        })->first();

                    if ($existing) {
                        $fail('Penghuni dengan ID ' . $value . ' telah memiliki kontrak aktif dengan tipe kontrak yang sama.');
                    }
                }
            ],
            'penghuni_2' => 'nullable|exists:penghuni,id|different:penghuni_1|different:penghuni_3|different:penghuni_4',
            'penghuni_3' => 'nullable|exists:penghuni,id|different:penghuni_1|different:penghuni_2|different:penghuni_4',
            'penghuni_4' => 'nullable|exists:penghuni,id|different:penghuni_1|different:penghuni_2|different:penghuni_3',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute harus diisi.',
            'exists' => ':attribute tidak ditemukan.',
            'integer' => ':attribute harus berupa angka.',
            'string' => ':attribute harus berupa teks.',
            'date' => ':attribute harus berupa tanggal.',
            'after_or_equal' => ':attribute harus setelah tanggal awal.',
            'unique' => ':attribute sudah terdaftar.',
            'in' => ':attribute tidak valid.',
            'numeric' => ':attribute harus berupa angka.',
            'different' => ':attribute tidak boleh sama dengan :other.',
            'max' => ':attribute maksimal :max karakter.',

            'penghuni_1.different' => 'Penghuni 1 dan penghuni 2 tidak boleh sama.',
            'penghuni_2.different' => 'Penghuni 1 dan penghuni 2 tidak boleh sama.',
            'penghuni_3.different' => 'Penghuni 1 dan penghuni 3 tidak boleh sama.',
            'penghuni_4.different' => 'Penghuni 1 dan penghuni 4 tidak boleh sama.',
        ];
    }
}
