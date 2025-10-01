<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttachmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'store_id' => $this->store_id,
            'attachable_type' => $this->attachable_type,
            'attachable_id' => $this->attachable_id,
            'uploaded_by' => $this->uploaded_by,
            'disk' => $this->disk,
            'path' => $this->path,
            'original_filename' => $this->original_filename,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'checksum' => $this->checksum,
            'metadata' => $this->metadata,
            'created_at' => optional($this->created_at)?->toDateTimeString(),
        ];
    }
}
