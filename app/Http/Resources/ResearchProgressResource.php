<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResearchProgressResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'proposal_id' => $this->research_proposal_id,
            'judul_progres' => $this->judul_progres,
            'deskripsi' => $this->deskripsi,
            'status' => $this->status,
            'periode' => $this->periode,
            'documents' => ProgressDocumentResource::collection($this->whenLoaded('documents')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
