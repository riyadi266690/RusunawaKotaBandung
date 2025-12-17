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
            'penghuni_id1' => [
                'required',
                'exists:penghuni,id',
                function ($attribute, $value, $fail) {
                    $existing = Kontrak::where('status_kontrak', 1)
                        ->where('tipe_kontrak', $this->tipe_kontrak)
                        ->where(function ($query) use ($value) {
                            $query->where('penghuni_id1', $value)
                                ->orWhere('penghuni_id2', $value)
                                ->orWhere('penghuni_id3', $value)
                                ->orWhere('penghuni_id4', $value);
                        })->first();

                    if ($existing) {
                        $fail('Penghuni dengan ID ' . $value . ' telah memiliki kontrak aktif dengan tipe kontrak yang sama.');
                    }
                }
            ],
            'penghuni_id2' => 'nullable|exists:penghuni,id|different:penghuni_id1|different:penghuni_id3|different:penghuni_id4',
            'penghuni_id3' => 'nullable|exists:penghuni,id|different:penghuni_id1|different:penghuni_id2|different:penghuni_id4',
            'penghuni_id4' => 'nullable|exists:penghuni,id|different:penghuni_id1|different:penghuni_id2|different:penghuni_id3',
        ];
    }
}
