<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GuidanceDocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uploader' => new UserResource($this->whenLoaded('uploader')),
            'file_url' => url("api/files/{$this->file_path}"),
            'original_name' => $this->original_name,
            'file_size' => $this->file_size,
            'file_type' => $this->file_type,
            'version' => $this->version,
            'created_at' => $this->created_at,
        ];
    }
}
