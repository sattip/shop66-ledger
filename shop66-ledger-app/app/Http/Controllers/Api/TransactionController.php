<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\AuthorizesStoreAccess;
use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Models\Store;
use App\Models\Transaction;
use App\Services\Transactions\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
    use AuthorizesStoreAccess;

    public function __construct(private readonly TransactionService $service)
    {
    }

    public function index(Request $request, Store $store)
    {
        $this->authorizeStore($request, $store);
        $query = $store->transactions()->with(['vendor', 'customer', 'account', 'lines']);

        if ($type = $request->query('type')) {
            $query->where('type', $type);
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('transaction_date', '>=', $request->query('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('transaction_date', '<=', $request->query('date_to'));
        }

        return TransactionResource::collection(
            $query->orderByDesc('transaction_date')->paginate()
        );
    }

    public function store(Request $request, Store $store): TransactionResource
    {
        $this->authorizeStore($request, $store);
        $data = $this->validateTransaction($request, $store);
        $transaction = $this->service->create($store, $data);

        return new TransactionResource($transaction);
    }

    public function show(Request $request, Store $store, Transaction $transaction): TransactionResource
    {
        $this->authorizeStore($request, $store);
        return new TransactionResource($transaction->load(['vendor', 'customer', 'account', 'lines']));
    }

    public function update(Request $request, Store $store, Transaction $transaction): TransactionResource
    {
        $this->authorizeStore($request, $store);
        $data = $this->validateTransaction($request, $store, $transaction);
        $transaction = $this->service->update($transaction, $data);

        return new TransactionResource($transaction);
    }

    public function destroy(Request $request, Store $store, Transaction $transaction): JsonResponse
    {
        $this->authorizeStore($request, $store);
        $transaction->delete();

        return response()->json(['message' => 'Transaction deleted']);
    }

    private function validateTransaction(Request $request, Store $store, ?Transaction $transaction = null): array
    {
        return $request->validate([
            'account_id' => ['nullable', Rule::exists('accounts', 'id')->where('store_id', $store->id)],
            'category_id' => ['nullable', Rule::exists('categories', 'id')->where('store_id', $store->id)],
            'vendor_id' => ['nullable', Rule::exists('vendors', 'id')->where('store_id', $store->id)],
            'customer_id' => ['nullable', Rule::exists('customers', 'id')->where('store_id', $store->id)],
            'user_id' => ['nullable', 'exists:users,id'],
            'approved_by' => ['nullable', 'exists:users,id'],
            'type' => ['required', 'string', 'max:32'],
            'status' => ['nullable', 'string', 'max:32'],
            'reference' => ['nullable', 'string', 'max:100', Rule::unique('transactions', 'reference')->where('store_id', $store->id)->ignore($transaction)],
            'external_id' => ['nullable', 'string', 'max:100'],
            'transaction_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:transaction_date'],
            'currency_code' => ['nullable', 'string', 'size:3'],
            'exchange_rate' => ['nullable', 'numeric'],
            'memo' => ['nullable', 'string'],
            'approved_at' => ['nullable', 'date'],
            'posted_at' => ['nullable', 'date'],
            'document_id' => ['nullable', Rule::exists('documents', 'id')->where('store_id', $store->id)],
            'metadata' => ['nullable', 'array'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.item_id' => ['nullable', Rule::exists('items', 'id')->where('store_id', $store->id)],
            'lines.*.category_id' => ['nullable', Rule::exists('categories', 'id')->where('store_id', $store->id)],
            'lines.*.description' => ['nullable', 'string'],
            'lines.*.quantity' => ['nullable', 'numeric', 'min:0'],
            'lines.*.unit_price' => ['nullable', 'numeric'],
            'lines.*.discount_rate' => ['nullable', 'numeric', 'min:0'],
            'lines.*.tax_rate' => ['nullable', 'numeric', 'min:0'],
            'lines.*.tax_amount' => ['nullable', 'numeric'],
            'lines.*.total' => ['nullable', 'numeric'],
            'lines.*.metadata' => ['nullable', 'array'],
        ]);
    }
}
