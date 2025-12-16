<?php

namespace App\Http\Resources\Pendaftaran;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PendaftaranResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id_pendaftaran' => $this->id,
            'nama_pendaftar' => $this->nama,
            'nomor_pendaftar' => $this->telp_pendaftar,
            'surat_keterangan' => $this->suket,
            'status_pendaftaran' => $this->status_daftar,
            'tanggal_daftar' => $this->tgl_daftar,
            'tanggal_wawancara' => $this->tgl_wawancara,
            'tanggal_selesai' => $this->tgl_final,
            'keterangan_wawancara' => $this->ket_wawancara
        ];
    }
}
