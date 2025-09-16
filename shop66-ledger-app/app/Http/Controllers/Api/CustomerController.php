<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\AuthorizesStoreAccess;
use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    use AuthorizesStoreAccess;

    public function index(Request $request, Store $store)
    {
        $this->authorizeStore($request, $store);
        $customers = $store->customers()->orderBy('name')->paginate();

        return CustomerResource::collection($customers);
    }

    public function store(Request $request, Store $store): CustomerResource
    {
        $this->authorizeStore($request, $store);
        $data = $this->validateCustomer($request, $store);
        $customer = $store->customers()->create($data);

        return new CustomerResource($customer);
    }

    public function show(Request $request, Store $store, Customer $customer): CustomerResource
    {
        $this->authorizeStore($request, $store);
        return new CustomerResource($customer);
    }

    public function update(Request $request, Store $store, Customer $customer): CustomerResource
    {
        $this->authorizeStore($request, $store);
        $data = $this->validateCustomer($request, $store, $customer);
        $customer->fill($data)->save();

        return new CustomerResource($customer);
    }

    public function destroy(Request $request, Store $store, Customer $customer): JsonResponse
    {
        $this->authorizeStore($request, $store);
        $customer->delete();

        return response()->json(['message' => 'Customer deleted']);
    }

    private function validateCustomer(Request $request, Store $store, ?Customer $customer = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('customers', 'slug')->where('store_id', $store->id)->ignore($customer)],
            'customer_code' => ['nullable', 'string', 'max:100', Rule::unique('customers', 'customer_code')->where('store_id', $store->id)->ignore($customer)],
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
