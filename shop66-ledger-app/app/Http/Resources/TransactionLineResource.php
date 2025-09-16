<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionLineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'transaction_id' => $this->transaction_id,
            'item_id' => $this->item_id,
            'category_id' => $this->category_id,
            'line_number' => $this->line_number,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'discount_rate' => $this->discount_rate,
            'tax_rate' => $this->tax_rate,
            'tax_amount' => $this->tax_amount,
            'total' => $this->total,
            'metadata' => $this->metadata,
        ];
    }
}
