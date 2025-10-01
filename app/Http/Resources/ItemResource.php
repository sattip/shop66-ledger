<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'store_id' => $this->store_id,
            'category_id' => $this->category_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'unit' => $this->unit,
            'default_price' => $this->default_price,
            'default_tax_rate' => $this->default_tax_rate,
            'is_service' => $this->is_service,
            'is_active' => $this->is_active,
            'metadata' => $this->metadata,
            'category' => new CategoryResource($this->whenLoaded('category')),
        ];
    }
}
