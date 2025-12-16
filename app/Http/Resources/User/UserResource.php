<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id_user' => $this->id,
            'nama_user' => $this->name,
            'email_user' => $this->email,
            'role_user' => new RoleResource($this->whenLoaded('role')),
        ];
    }
}
