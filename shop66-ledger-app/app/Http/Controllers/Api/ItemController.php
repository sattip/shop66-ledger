<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\AuthorizesStoreAccess;
use App\Http\Controllers\Controller;
use App\Http\Resources\ItemResource;
use App\Models\Category;
use App\Models\Item;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ItemController extends Controller
{
    use AuthorizesStoreAccess;

    public function index(Request $request, Store $store)
    {
        $this->authorizeStore($request, $store);
        $items = $store->items()->with('category')->orderBy('name')->paginate();

        return ItemResource::collection($items);
    }

    public function store(Request $request, Store $store): ItemResource
    {
        $this->authorizeStore($request, $store);
        $data = $this->validateItem($request, $store);
        $item = $store->items()->create($data);

        return new ItemResource($item->load('category'));
    }

    public function show(Request $request, Store $store, Item $item): ItemResource
    {
        $this->authorizeStore($request, $store);
        return new ItemResource($item->load('category'));
    }

    public function update(Request $request, Store $store, Item $item): ItemResource
    {
        $this->authorizeStore($request, $store);
        $data = $this->validateItem($request, $store, $item);
        $item->fill($data)->save();

        return new ItemResource($item->load('category'));
    }

    public function destroy(Request $request, Store $store, Item $item): JsonResponse
    {
        $this->authorizeStore($request, $store);
        $item->delete();

        return response()->json(['message' => 'Item deleted']);
    }

    private function validateItem(Request $request, Store $store, ?Item $item = null): array
    {
        return $request->validate([
            'category_id' => ['nullable', Rule::exists('categories', 'id')->where('store_id', $store->id)],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('items', 'slug')->where('store_id', $store->id)->ignore($item)],
            'sku' => ['nullable', 'string', 'max:100', Rule::unique('items', 'sku')->where('store_id', $store->id)->ignore($item)],
            'unit' => ['nullable', 'string', 'max:30'],
            'default_price' => ['nullable', 'numeric'],
            'default_tax_rate' => ['nullable', 'numeric'],
            'is_service' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'metadata' => ['nullable', 'array'],
        ]);
    }
}
