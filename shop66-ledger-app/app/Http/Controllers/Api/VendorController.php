<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\AuthorizesStoreAccess;
use App\Http\Controllers\Controller;
use App\Http\Resources\VendorResource;
use App\Models\Store;
use App\Models\Vendor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VendorController extends Controller
{
    use AuthorizesStoreAccess;

    public function index(Request $request, Store $store)
    {
        $this->authorizeStore($request, $store);
        $vendors = $store->vendors()->orderBy('name')->paginate();

        return VendorResource::collection($vendors);
    }

    public function store(Request $request, Store $store): VendorResource
    {
        $this->authorizeStore($request, $store);
        $data = $this->validateVendor($request, $store);
        $vendor = $store->vendors()->create($data);

        return new VendorResource($vendor);
    }

    public function show(Request $request, Store $store, Vendor $vendor): VendorResource
    {
        $this->authorizeStore($request, $store);
        return new VendorResource($vendor);
    }

    public function update(Request $request, Store $store, Vendor $vendor): VendorResource
    {
        $this->authorizeStore($request, $store);
        $data = $this->validateVendor($request, $store, $vendor);
        $vendor->fill($data)->save();

        return new VendorResource($vendor);
    }

    public function destroy(Request $request, Store $store, Vendor $vendor): JsonResponse
    {
        $this->authorizeStore($request, $store);
        $vendor->delete();

        return response()->json(['message' => 'Vendor deleted']);
    }

    private function validateVendor(Request $request, Store $store, ?Vendor $vendor = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('vendors', 'slug')->where('store_id', $store->id)->ignore($vendor)],
            'tax_id' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'website' => ['nullable', 'url'],
            'currency_code' => ['nullable', 'string', 'size:3'],
            'address_line1' => ['nullable', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'postal_code' => ['nullable', 'string', 'max:50'],
            'country_code' => ['nullable', 'string', 'size:2'],
            'notes' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}
