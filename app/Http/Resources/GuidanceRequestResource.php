<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GuidanceRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'proposal_id' => $this->research_proposal_id,
            'mahasiswa' => new UserResource($this->whenLoaded('mahasiswa')),
            'dosen' => new UserResource($this->whenLoaded('dosen')),
            'judul' => $this->judul,
            'deskripsi' => $this->deskripsi,
            'status' => $this->status,
            'documents' => GuidanceDocumentResource::collection($this->whenLoaded('documents')),
            'comments' => GuidanceCommentResource::collection($this->whenLoaded('comments')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
