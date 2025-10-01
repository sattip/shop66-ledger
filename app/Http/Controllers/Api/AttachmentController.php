<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\AuthorizesStoreAccess;
use App\Http\Controllers\Controller;
use App\Http\Resources\AttachmentResource;
use App\Models\Attachment;
use App\Models\Document;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    use AuthorizesStoreAccess;

    public function index(Request $request, Store $store, Document $document)
    {
        $this->authorizeStore($request, $store);

        return AttachmentResource::collection(
            $document->attachments()->orderByDesc('created_at')->paginate()
        );
    }

    public function store(Request $request, Store $store, Document $document): AttachmentResource
    {
        $this->authorizeStore($request, $store);
        $data = $request->validate([
            'file' => ['required', 'file', 'max:40960'],
            'metadata' => ['nullable', 'array'],
        ]);

        $file = $request->file('file');
        $disk = 'local';
        $path = $file->store('attachments/'.$store->id, $disk);
        $checksum = hash_file('sha256', Storage::disk($disk)->path($path));

        $attachment = $document->attachments()->create([
            'store_id' => $store->id,
            'uploaded_by' => $request->user()->id,
            'disk' => $disk,
            'path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'checksum' => $checksum,
            'metadata' => $data['metadata'] ?? null,
        ]);

        return new AttachmentResource($attachment);
    }

    public function destroy(Request $request, Store $store, Document $document, Attachment $attachment): JsonResponse
    {
        $this->authorizeStore($request, $store);
        Storage::disk($attachment->disk)->delete($attachment->path);
        $attachment->delete();

        return response()->json(['message' => 'Attachment removed']);
    }
}
