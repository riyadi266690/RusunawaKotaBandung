<?php

namespace App\Http\Requests\Pendaftar;

use App\Models\Pendaftaran;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTanggalSelesaiPendaftaran extends FormRequest
{
    protected $tglWawancara;
    public function authorize(): bool
    {

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    protected function prepareForValidation(): void
    {
        $pendaftar = Pendaftaran::find($this->route('id'));
        $this->tglWawancara = $pendaftar?->tgl_wawancara;
    }

    public function rules(): array
    {
        return [
            'tgl_final' => [
                'required|date',
                'after_or_equal:' . $this->tglWawancara
            ],
            'ket_wawancara' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'tgl_final.required' => 'Tanggal selesai harus diisi.',
            'tgl_final.date' => 'Format tanggal tidak valid.',
            'tgl_final.after_or_equal' => 'Tanggal selesai tidak boleh lebih awal dari tanggal wawancara.',
            'ket_wawancara.string' => 'Catatan wawancara harus berupa teks.',
        ];
    }
}
