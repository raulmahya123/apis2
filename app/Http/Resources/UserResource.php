<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'nomor_induk' => $this->nomor_induk,
            'jurusan' => $this->jurusan,
            'angkatan' => $this->angkatan,
            'no_telepon' => $this->no_telepon,
            'avatar' => $this->avatar ? url("storage/{$this->avatar}") : null,
            'created_at' => $this->created_at,
        ];
    }
}
