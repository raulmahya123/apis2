<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResearchSupervisorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'dosen' => new UserResource($this->whenLoaded('dosen')),
            'status' => $this->status,
            'keterangan' => $this->keterangan,
            'assigned_by' => new UserResource($this->whenLoaded('assignedBy')),
            'assigned_at' => $this->assigned_at,
            'responded_at' => $this->responded_at,
        ];
    }
}
