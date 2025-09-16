<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'store_id' => $this->store_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'customer_code' => $this->customer_code,
            'email' => $this->email,
            'phone' => $this->phone,
            'website' => $this->website,
            'currency_code' => $this->currency_code,
            'address_line1' => $this->address_line1,
            'address_line2' => $this->address_line2,
            'city' => $this->city,
            'state' => $this->state,
            'postal_code' => $this->postal_code,
            'country_code' => $this->country_code,
            'notes' => $this->notes,
            'metadata' => $this->metadata,
            'is_active' => $this->is_active,
        ];
    }
}
