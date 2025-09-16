<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\AuthorizesStoreAccess;
use App\Http\Controllers\Controller;
use App\Http\Resources\DocumentIngestionResource;
use App\Models\Document;
use App\Models\DocumentIngestion;
use App\Models\Store;
use App\Services\Documents\DocumentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentIngestionController extends Controller
{
    use AuthorizesStoreAccess;

    public function __construct(private readonly DocumentService $service)
    {
    }

    public function index(Request $request, Store $store, Document $document)
    {
        $this->authorizeStore($request, $store);
        return DocumentIngestionResource::collection(
            $document->ingestions()->orderByDesc('created_at')->paginate()
        );
    }

    public function show(Request $request, Store $store, Document $document, DocumentIngestion $ingestion): DocumentIngestionResource
    {
        $this->authorizeStore($request, $store);
        return new DocumentIngestionResource($ingestion);
    }

    public function store(Request $request, Store $store, Document $document): JsonResponse
    {
        $this->authorizeStore($request, $store);
        $this->service->reprocess($document);

        return response()->json(['message' => 'Document queued for reprocessing']);
    }
}
