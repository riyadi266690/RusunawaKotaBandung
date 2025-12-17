<?php

namespace App\Http\Resources\Kontrak;

use Illuminate\Http\Request;
use App\Http\Resources\Unit\UnitResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Penghuni\PenghuniResource;

class KontrakResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id_kontrak' => $this->id,
            'nama_pihak' => $this->nama_pihak,
            'nomor_kontrak' => $this->no_kontrak,
            'status_kontrak' => $this->status_kontrak,
            'tipe_kontrak' => $this->tipe_kontrak,
            'tanggal_awal' => $this->tgl_awal,
            'tanggal_akhir' => $this->tgl_akhir,
            'tanggal_keluar' => $this->tgl_keluar,
            'status_tanda_tangan' => $this->status_ttd,
            'harga_sewa' => $this->harga_sewa,
            'jenis_usaha' => $this->jenis_usaha,
            'luas_usaha' => $this->luas_usaha,
            'dokumen_kontrak' => $this->dok_kontrak,
            'penghuni_1' => new PenghuniResource($this->whenLoaded('penghuni')),
            'penghuni_2' => new PenghuniResource($this->whenLoaded('penghuni')),
            'penghuni_3' => new PenghuniResource($this->whenLoaded('penghuni')),
            'penghuni_4' => new PenghuniResource($this->whenLoaded('penghuni')),
            'unit' => new UnitResource($this->whenLoaded('unit')),
        ];
    }
}
