<?php

namespace App\Http\Resources\Gedung;

use App\Http\Resources\Lokasi\LokasiResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GedungResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id_gedung' => $this->id,
            'nama_gedung' => $this->nama_gedung,
            'tipe_gedung' => $this->type_gedung,
            'alamat_gedung' => new LokasiResource($this->whenLoaded('lokasi')),
        ];
    }
}
