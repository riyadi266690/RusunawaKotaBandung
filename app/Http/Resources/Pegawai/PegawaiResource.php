<?php

namespace App\Http\Resources\Pegawai;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PegawaiResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id_pegawai' => $this->id,
            'pegawai' => $this->nama_pegawai,
            'nomor_hp' => $this->no_hp,
            'posisi_pegawai' => $this->posisi
        ];
    }
}
