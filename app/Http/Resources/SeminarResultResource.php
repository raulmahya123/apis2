<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SeminarResultResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'penguji' => new UserResource($this->whenLoaded('penguji')),
            'nilai' => $this->nilai,
            'catatan' => $this->catatan,
            'keputusan' => $this->keputusan,
            'file_berita_acara' => $this->file_berita_acara ? url("api/files/{$this->file_berita_acara}") : null,
            'created_at' => $this->created_at,
        ];
    }
}
