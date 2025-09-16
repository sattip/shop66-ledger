<?php

namespace App\Services\Documents;

use App\Models\Category;
use App\Models\Document;
use App\Models\Item;
use App\Models\Store;
use App\Models\Vendor;
use Illuminate\Support\Arr;

class MatchingService
{
    public function match(Document $document, array $extraction): array
    {
        $store = $document->store;

        $vendor = $this->matchVendor($store, Arr::get($extraction, 'vendor.name'));
        $lineItems = array_map(function ($line) use ($store) {
            return $this->matchLine($store, $line);
        }, Arr::get($extraction, 'line_items', []));

        return [
            'status' => 'matched',
            'vendor_id' => $vendor?->id,
            'line_items' => $lineItems,
        ];
    }

    private function matchVendor(Store $store, ?string $name): ?Vendor
    {
        if (! $name) {
            return null;
        }

        return $store->vendors()->whereRaw('LOWER(name) = ?', [strtolower($name)])->first();
    }

    private function matchLine(Store $store, array $line): array
    {
        $description = Arr::get($line, 'description');

        $item = $description ? $this->matchItem($store, $description) : null;
        $category = $item?->category;

        if (! $category && $description) {
            $category = $this->matchCategory($store, $description);
        }

        return array_merge($line, [
            'item_id' => $item?->id,
            'category_id' => $category?->id,
        ]);
    }

    private function matchItem(Store $store, string $description): ?Item
    {
        return $store->items()
            ->whereRaw('LOWER(name) = ?', [strtolower($description)])
            ->orWhereRaw('LOWER(sku) = ?', [strtolower($description)])
            ->first();
    }

    private function matchCategory(Store $store, string $description): ?Category
    {
        return $store->categories()
            ->whereRaw('LOWER(name) = ?', [strtolower($description)])
            ->first();
    }
}
