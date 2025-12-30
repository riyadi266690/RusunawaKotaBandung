<?php

namespace App\Http\Resources\Lokasi;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LokasiResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id_lokasi' => $this->id,
            'nama_lokasi' => $this->nama_lokasi,
            'kepala_lokasi' => $this->kepala_lokasi,
            'alamat_lokasi' => $this->alamat_lokasi,
        ];
    }
}
