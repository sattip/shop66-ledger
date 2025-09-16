<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\AuthorizesStoreAccess;
use App\Http\Controllers\Controller;
use App\Http\Resources\BudgetResource;
use App\Models\Budget;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BudgetController extends Controller
{
    use AuthorizesStoreAccess;

    public function index(Request $request, Store $store)
    {
        $this->authorizeStore($request, $store);
        $budgets = $store->budgets()->with(['category', 'account'])->orderByDesc('period_start')->paginate();

        return BudgetResource::collection($budgets);
    }

    public function store(Request $request, Store $store): BudgetResource
    {
        $this->authorizeStore($request, $store);
        $data = $this->validateBudget($request, $store);
        $budget = $store->budgets()->create($data);

        return new BudgetResource($budget->load(['category', 'account']));
    }

    public function show(Request $request, Store $store, Budget $budget): BudgetResource
    {
        $this->authorizeStore($request, $store);
        return new BudgetResource($budget->load(['category', 'account']));
    }

    public function update(Request $request, Store $store, Budget $budget): BudgetResource
    {
        $this->authorizeStore($request, $store);
        $data = $this->validateBudget($request, $store, $budget);
        $budget->fill($data)->save();

        return new BudgetResource($budget->load(['category', 'account']));
    }

    public function destroy(Request $request, Store $store, Budget $budget): JsonResponse
    {
        $this->authorizeStore($request, $store);
        $budget->delete();

        return response()->json(['message' => 'Budget deleted']);
    }

    private function validateBudget(Request $request, Store $store, ?Budget $budget = null): array
    {
        return $request->validate([
            'category_id' => ['nullable', Rule::exists('categories', 'id')->where('store_id', $store->id)],
            'account_id' => ['nullable', Rule::exists('accounts', 'id')->where('store_id', $store->id)],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'amount' => ['required', 'numeric'],
            'actual' => ['nullable', 'numeric'],
            'currency_code' => ['nullable', 'string', 'size:3'],
            'status' => ['nullable', 'string', Rule::in(['draft', 'active', 'closed'])],
            'metadata' => ['nullable', 'array'],
        ]);
    }
}
