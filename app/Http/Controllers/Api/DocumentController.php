<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\AuthorizesStoreAccess;
use App\Http\Controllers\Controller;
use App\Http\Resources\DocumentResource;
use App\Models\Document;
use App\Models\Store;
use App\Services\Documents\DocumentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class DocumentController extends Controller
{
    use AuthorizesStoreAccess;

    public function __construct(private readonly DocumentService $service) {}

    public function index(Request $request, Store $store)
    {
        $this->authorizeStore($request, $store);
        $query = $store->documents()->with(['vendor', 'ingestions']);

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->query('vendor_id'));
        }

        return DocumentResource::collection(
            $query->orderByDesc('created_at')->paginate()
        );
    }

    public function store(Request $request, Store $store): DocumentResource
    {
        $this->authorizeStore($request, $store);
        $data = $request->validate([
            'file' => ['required', 'file', 'max:40960'],
            'vendor_id' => ['nullable', Rule::exists('vendors', 'id')->where('store_id', $store->id)],
            'document_type' => ['nullable', 'string', 'max:32'],
            'document_number' => ['nullable', 'string', 'max:100'],
            'document_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
            'currency_code' => ['nullable', 'string', 'size:3'],
            'memo' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
        ]);

        $file = $request->file('file');
        $attributes = Arr::except($data, ['file']);
        $attributes['uploaded_by'] = $request->user()->id;

        $document = $this->service->upload($store, $file, $attributes);

        return new DocumentResource($document->load(['vendor', 'ingestions', 'lines']));
    }

    public function show(Request $request, Store $store, Document $document): DocumentResource
    {
        $this->authorizeStore($request, $store);

        return new DocumentResource($document->load(['vendor', 'ingestions', 'lines']));
    }

    public function update(Request $request, Store $store, Document $document): DocumentResource
    {
        $this->authorizeStore($request, $store);
        $data = $request->validate([
            'vendor_id' => ['nullable', Rule::exists('vendors', 'id')->where('store_id', $store->id)],
            'status' => ['nullable', 'string', 'max:32'],
            'document_type' => ['nullable', 'string', 'max:32'],
            'document_number' => ['nullable', 'string', 'max:100'],
            'document_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
            'currency_code' => ['nullable', 'string', 'size:3'],
            'subtotal' => ['nullable', 'numeric'],
            'tax_total' => ['nullable', 'numeric'],
            'total' => ['nullable', 'numeric'],
            'memo' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
        ]);

        $document = $this->service->update($document, $data);

        return new DocumentResource($document->load(['vendor', 'ingestions', 'lines']));
    }

    public function destroy(Request $request, Store $store, Document $document): JsonResponse
    {
        $this->authorizeStore($request, $store);
        $this->service->delete($document);

        return response()->json(['message' => 'Document deleted']);
    }
}
