<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'store_id' => $this->store_id,
            'account_id' => $this->account_id,
            'category_id' => $this->category_id,
            'vendor_id' => $this->vendor_id,
            'customer_id' => $this->customer_id,
            'user_id' => $this->user_id,
            'approved_by' => $this->approved_by,
            'type' => $this->type,
            'status' => $this->status,
            'reference' => $this->reference,
            'external_id' => $this->external_id,
            'transaction_date' => $this->transaction_date?->toDateString(),
            'due_date' => $this->due_date?->toDateString(),
            'currency_code' => $this->currency_code,
            'exchange_rate' => $this->exchange_rate,
            'subtotal' => $this->subtotal,
            'tax_total' => $this->tax_total,
            'total' => $this->total,
            'balance' => $this->balance,
            'memo' => $this->memo,
            'approved_at' => optional($this->approved_at)?->toDateTimeString(),
            'posted_at' => optional($this->posted_at)?->toDateTimeString(),
            'metadata' => $this->metadata,
            'vendor' => new VendorResource($this->whenLoaded('vendor')),
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'account' => new AccountResource($this->whenLoaded('account')),
            'lines' => TransactionLineResource::collection($this->whenLoaded('lines')),
        ];
    }
}
