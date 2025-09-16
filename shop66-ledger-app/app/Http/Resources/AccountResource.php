<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'store_id' => $this->store_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'account_number' => $this->account_number,
            'type' => $this->type,
            'currency_code' => $this->currency_code,
            'opening_balance' => $this->opening_balance,
            'current_balance' => $this->current_balance,
            'is_primary' => $this->is_primary,
            'is_active' => $this->is_active,
            'settings' => $this->settings,
        ];
    }
}
