<?php

namespace App\Http\Resources\Penghuni;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PenghuniResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id_penghuni' => $this->id,
            'nik' => $this->nik,
            'nama_penghuni' => $this->nama,
            'email' => $this->email,
            'tanggal_lahir' => $this->tgl_lahir,
            'tempat_lahir' => $this->tempat_lahir,
            'nomor_telepon' => $this->no_tlp,
            'jenis_kelamin' => $this->jenis_kelamin,
            'status_kawin' => $this->status_kawin,
            'agama' => $this->agama,
            'alamat_penghuni' => $this->alamat,
        ];
    }
}
