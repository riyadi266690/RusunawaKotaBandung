<?php

namespace App\Http\Resources\Unit;

use Illuminate\Http\Request;
use App\Http\Resources\Gedung\GedungResource;
use Illuminate\Http\Resources\Json\JsonResource;

class UnitResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'nomor_unit' => $this->nomor,
            'lantai' => $this->lantai,
            'tipe_unit' => $this->tipe_unit,
            'status_jual' => $this->status_jual,
            'gedung' => new GedungResource($this->whenLoaded('gedung')),
        ];
    }
}
