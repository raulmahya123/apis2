<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SeminarResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'proposal_id' => $this->research_proposal_id,
            'mahasiswa' => new UserResource($this->whenLoaded('mahasiswa')),
            'jenis' => $this->jenis,
            'status' => $this->status,
            'judul' => $this->judul,
            'deskripsi' => $this->deskripsi,
            'file_url' => $this->file_path ? url("storage/{$this->file_path}") : null,
            'syarat_valid' => $this->syarat_valid,
            'tanggal_pengajuan' => $this->tanggal_pengajuan,
            'tanggal_seminar' => $this->tanggal_seminar,
            'ruangan' => $this->ruangan,
            'catatan' => $this->catatan,
            'reviewers' => SeminarReviewerResource::collection($this->whenLoaded('reviewers')),
            'results' => SeminarResultResource::collection($this->whenLoaded('results')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
