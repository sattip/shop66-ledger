<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BudgetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'store_id' => $this->store_id,
            'category_id' => $this->category_id,
            'account_id' => $this->account_id,
            'period_start' => $this->period_start?->toDateString(),
            'period_end' => $this->period_end?->toDateString(),
            'amount' => $this->amount,
            'actual' => $this->actual,
            'currency_code' => $this->currency_code,
            'status' => $this->status,
            'metadata' => $this->metadata,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'account' => new AccountResource($this->whenLoaded('account')),
        ];
    }
}
