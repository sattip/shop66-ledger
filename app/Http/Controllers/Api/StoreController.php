<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Resources\StoreResource;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StoreController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Store::query()->with('taxRegion');

        if (! $user->hasAnyRoleValue([UserRole::SUPER_ADMIN, UserRole::ADMIN])) {
            $storeIds = $user->stores()->pluck('stores.id');
            $query->whereIn('id', $storeIds);
        }

        return StoreResource::collection($query->latest()->paginate());
    }

    public function store(Request $request): StoreResource
    {
        $data = $this->validateStore($request);
        $store = Store::create($data);

        return new StoreResource($store->fresh(['taxRegion']));
    }

    public function show(Store $store): StoreResource
    {
        return new StoreResource($store->load('taxRegion'));
    }

    public function update(Request $request, Store $store): StoreResource
    {
        $data = $this->validateStore($request, $store);
        $store->fill($data)->save();

        return new StoreResource($store->fresh(['taxRegion']));
    }

    public function destroy(Store $store): JsonResponse
    {
        $store->delete();

        return response()->json(['message' => 'Store deleted']);
    }

    private function validateStore(Request $request, ?Store $store = null): array
    {
        return $request->validate([
            'tax_region_id' => ['nullable', 'exists:tax_regions,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('stores', 'code')->ignore($store)],
            'currency_code' => ['required', 'string', 'size:3'],
            'timezone' => ['required', 'string', 'max:64'],
            'tax_id' => ['nullable', 'string', 'max:100'],
            'contact_email' => ['nullable', 'email'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'address_line1' => ['nullable', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'postal_code' => ['nullable', 'string', 'max:50'],
            'country_code' => ['nullable', 'string', 'size:2'],
            'default_tax_rate' => ['nullable', 'numeric', 'between:0,100'],
            'settings' => ['nullable', 'array'],
        ]);
    }
}
