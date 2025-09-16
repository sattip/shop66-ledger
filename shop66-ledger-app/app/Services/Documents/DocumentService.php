<?php

namespace App\Services\Documents;

use App\Models\Document;
use App\Models\Store;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentService
{
    public function __construct(private readonly DocumentProcessingPipeline $pipeline)
    {
    }

    public function upload(Store $store, UploadedFile $file, array $attributes = []): Document
    {
        return DB::transaction(function () use ($store, $file, $attributes) {
            $disk = $attributes['disk'] ?? 'local';
            $path = $file->store('documents/'.$store->id, $disk);
            $checksum = hash_file('sha256', Storage::disk($disk)->path($path));

            $document = $store->documents()->create(array_merge($attributes, [
                'disk' => $disk,
                'path' => $path,
                'original_filename' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
                'checksum' => $checksum,
                'status' => $attributes['status'] ?? 'uploaded',
                'source_type' => $attributes['source_type'] ?? 'upload',
            ]));

            $this->pipeline->dispatch($document);

            return $document->fresh();
        });
    }

    public function update(Document $document, array $data): Document
    {
        $document->fill($data)->save();

        return $document->fresh();
    }

    public function reprocess(Document $document): void
    {
        $document->ingestions()->update(['status' => 'archived']);
        $document->update(['status' => 'uploaded']);

        $this->pipeline->dispatch($document);
    }

    public function attachPayload(Document $document, array $payload): Document
    {
        $document->forceFill([
            'extraction_payload' => $payload,
            'status' => $payload['status'] ?? $document->status,
        ])->save();

        return $document->fresh();
    }

    public function delete(Document $document): void
    {
        Storage::disk($document->disk)->delete($document->path);
        $document->delete();
    }
}
