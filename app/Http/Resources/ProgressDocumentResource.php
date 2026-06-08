<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProgressDocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'file_url' => url("storage/{$this->file_path}"),
            'original_name' => $this->original_name,
            'file_size' => $this->file_size,
            'file_type' => $this->file_type,
            'keterangan' => $this->keterangan,
            'created_at' => $this->created_at,
        ];
    }
}
