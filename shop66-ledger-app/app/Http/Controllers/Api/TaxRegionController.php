<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaxRegionResource;
use App\Models\TaxRegion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaxRegionController extends Controller
{
    public function index()
    {
        return TaxRegionResource::collection(
            TaxRegion::query()->orderBy('name')->paginate()
        );
    }

    public function store(Request $request): TaxRegionResource
    {
        $data = $this->validateRegion($request);
        $region = TaxRegion::create($data);

        return new TaxRegionResource($region);
    }

    public function show(TaxRegion $taxRegion): TaxRegionResource
    {
        return new TaxRegionResource($taxRegion);
    }

    public function update(Request $request, TaxRegion $taxRegion): TaxRegionResource
    {
        $data = $this->validateRegion($request, $taxRegion);
        $taxRegion->fill($data)->save();

        return new TaxRegionResource($taxRegion);
    }

    public function destroy(TaxRegion $taxRegion): JsonResponse
    {
        $taxRegion->delete();

        return response()->json(['message' => 'Tax region deleted']);
    }

    private function validateRegion(Request $request, ?TaxRegion $taxRegion = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('tax_regions', 'code')->ignore($taxRegion)],
            'country_code' => ['required', 'string', 'size:2'],
            'region' => ['nullable', 'string', 'max:100'],
            'default_rate' => ['nullable', 'numeric'],
            'settings' => ['nullable', 'array'],
        ]);
    }
}
