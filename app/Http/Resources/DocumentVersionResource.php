<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentVersionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uploader' => new UserResource($this->whenLoaded('uploader')),
            'file_url' => url("storage/{$this->file_path}"),
            'original_name' => $this->original_name,
            'version_number' => $this->version_number,
            'file_size' => $this->file_size,
            'file_type' => $this->file_type,
            'keterangan' => $this->keterangan,
            'created_at' => $this->created_at,
        ];
    }
}
