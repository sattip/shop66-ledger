<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\AuthorizesStoreAccess;
use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    use AuthorizesStoreAccess;

    public function index(Request $request, Store $store)
    {
        $this->authorizeStore($request, $store);
        $categories = $store->categories()
            ->with('parent')
            ->orderBy('display_order')
            ->orderBy('name')
            ->paginate();

        return CategoryResource::collection($categories);
    }

    public function store(Request $request, Store $store): CategoryResource
    {
        $this->authorizeStore($request, $store);
        $data = $this->validateCategory($request, $store);
        $category = $store->categories()->create($data);

        return new CategoryResource($category->fresh('parent'));
    }

    public function show(Request $request, Store $store, Category $category): CategoryResource
    {
        $this->authorizeStore($request, $store);

        return new CategoryResource($category->load('parent'));
    }

    public function update(Request $request, Store $store, Category $category): CategoryResource
    {
        $this->authorizeStore($request, $store);
        $data = $this->validateCategory($request, $store, $category);
        $category->fill($data)->save();

        return new CategoryResource($category->fresh('parent'));
    }

    public function destroy(Request $request, Store $store, Category $category): JsonResponse
    {
        $this->authorizeStore($request, $store);
        $category->delete();

        return response()->json(['message' => 'Category deleted']);
    }

    private function validateCategory(Request $request, Store $store, ?Category $category = null): array
    {
        return $request->validate([
            'parent_id' => ['nullable', Rule::exists('categories', 'id')->where('store_id', $store->id)],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('categories', 'slug')->where('store_id', $store->id)->ignore($category)],
            'type' => ['required', 'string', Rule::in(['expense', 'income', 'asset', 'liability'])],
            'description' => ['nullable', 'string'],
            'is_system' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'display_order' => ['nullable', 'integer', 'min:0'],
        ]);
    }
}
