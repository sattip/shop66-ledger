<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\AuthorizesStoreAccess;
use App\Http\Controllers\Controller;
use App\Http\Resources\AccountResource;
use App\Models\Account;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    use AuthorizesStoreAccess;

    public function index(Request $request, Store $store)
    {
        $this->authorizeStore($request, $store);
        return AccountResource::collection(
            $store->accounts()->orderBy('name')->paginate()
        );
    }

    public function store(Request $request, Store $store): AccountResource
    {
        $this->authorizeStore($request, $store);
        $data = $this->validateAccount($request, $store);
        $account = $store->accounts()->create($data);

        return new AccountResource($account);
    }

    public function show(Request $request, Store $store, Account $account): AccountResource
    {
        $this->authorizeStore($request, $store);
        return new AccountResource($account);
    }

    public function update(Request $request, Store $store, Account $account): AccountResource
    {
        $this->authorizeStore($request, $store);
        $data = $this->validateAccount($request, $store, $account);
        $account->fill($data)->save();

        return new AccountResource($account);
    }

    public function destroy(Request $request, Store $store, Account $account): JsonResponse
    {
        $this->authorizeStore($request, $store);
        $account->delete();

        return response()->json(['message' => 'Account deleted']);
    }

    private function validateAccount(Request $request, Store $store, ?Account $account = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('accounts', 'slug')->where('store_id', $store->id)->ignore($account)],
            'account_number' => ['nullable', 'string', 'max:100', Rule::unique('accounts', 'account_number')->where('store_id', $store->id)->ignore($account)],
            'type' => ['required', 'string', 'max:32'],
            'currency_code' => ['nullable', 'string', 'size:3'],
            'opening_balance' => ['nullable', 'numeric'],
            'current_balance' => ['nullable', 'numeric'],
            'is_primary' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'settings' => ['nullable', 'array'],
        ]);
    }
}
