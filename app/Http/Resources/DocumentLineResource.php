<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentLineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'document_id' => $this->document_id,
            'transaction_line_id' => $this->transaction_line_id,
            'item_id' => $this->item_id,
            'category_id' => $this->category_id,
            'line_number' => $this->line_number,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'tax_rate' => $this->tax_rate,
            'tax_amount' => $this->tax_amount,
            'total' => $this->total,
            'confidence' => $this->confidence,
            'status' => $this->status,
            'metadata' => $this->metadata,
        ];
    }
}
