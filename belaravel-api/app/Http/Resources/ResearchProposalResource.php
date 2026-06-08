<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResearchProposalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'mahasiswa' => new UserResource($this->whenLoaded('mahasiswa')),
            'judul' => $this->judul,
            'abstrak' => $this->abstrak,
            'topik' => $this->topik,
            'file_url' => $this->file_path ? url("storage/{$this->file_path}") : null,
            'status' => $this->status,
            'catatan_review' => $this->catatan_review,
            'reviewer' => new UserResource($this->whenLoaded('reviewer')),
            'reviewed_at' => $this->reviewed_at,
            'supervisors' => ResearchSupervisorResource::collection($this->whenLoaded('approvedSupervisors')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
