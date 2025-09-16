<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentIngestionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'document_id' => $this->document_id,
            'stage' => $this->stage,
            'status' => $this->status,
            'engine' => $this->engine,
            'message' => $this->message,
            'payload' => $this->payload,
            'ocr_text' => $this->ocr_text,
            'metrics' => $this->metrics,
            'started_at' => optional($this->started_at)?->toDateTimeString(),
            'completed_at' => optional($this->completed_at)?->toDateTimeString(),
        ];
    }
}
