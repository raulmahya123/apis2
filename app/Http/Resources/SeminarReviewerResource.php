<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SeminarReviewerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'dosen' => new UserResource($this->whenLoaded('dosen')),
            'peran' => $this->peran,
            'status' => $this->status,
        ];
    }
}
