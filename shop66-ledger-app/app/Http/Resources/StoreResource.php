<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tax_region_id' => $this->tax_region_id,
            'name' => $this->name,
            'code' => $this->code,
            'currency_code' => $this->currency_code,
            'timezone' => $this->timezone,
            'contact_email' => $this->contact_email,
            'contact_phone' => $this->contact_phone,
            'address' => [
                'line1' => $this->address_line1,
                'line2' => $this->address_line2,
                'city' => $this->city,
                'state' => $this->state,
                'postal_code' => $this->postal_code,
                'country_code' => $this->country_code,
            ],
            'default_tax_rate' => $this->default_tax_rate,
            'tax_region' => new TaxRegionResource($this->whenLoaded('taxRegion')),
            'settings' => $this->settings,
        ];
    }
}
