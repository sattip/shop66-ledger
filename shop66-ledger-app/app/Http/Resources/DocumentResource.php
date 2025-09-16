<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'store_id' => $this->store_id,
            'uploaded_by' => $this->uploaded_by,
            'vendor_id' => $this->vendor_id,
            'status' => $this->status,
            'source_type' => $this->source_type,
            'document_type' => $this->document_type,
            'document_number' => $this->document_number,
            'document_date' => $this->document_date?->toDateString(),
            'due_date' => $this->due_date?->toDateString(),
            'currency_code' => $this->currency_code,
            'subtotal' => $this->subtotal,
            'tax_total' => $this->tax_total,
            'total' => $this->total,
            'disk' => $this->disk,
            'path' => $this->path,
            'original_filename' => $this->original_filename,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'checksum' => $this->checksum,
            'ocr_language' => $this->ocr_language,
            'processed_at' => optional($this->processed_at)?->toDateTimeString(),
            'reviewed_at' => optional($this->reviewed_at)?->toDateTimeString(),
            'extraction_payload' => $this->extraction_payload,
            'metadata' => $this->metadata,
            'vendor' => new VendorResource($this->whenLoaded('vendor')),
            'lines' => DocumentLineResource::collection($this->whenLoaded('lines')),
            'ingestions' => DocumentIngestionResource::collection($this->whenLoaded('ingestions')),
        ];
    }
}
