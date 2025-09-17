<?php

namespace App\Services\Documents;

use App\Jobs\ProcessDocumentJob;
use App\Models\Document;
use App\Models\DocumentIngestion;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DocumentProcessingPipeline
{
    public function __construct(
        private readonly OcrManager $ocrManager,
        private readonly ExtractionService $extractionService,
        private readonly MatchingService $matchingService,
    ) {}

    public function dispatch(Document $document): void
    {
        ProcessDocumentJob::dispatch($document);
    }

    public function process(Document $document): void
    {
        DB::transaction(function () use ($document) {
            $ocrRecord = $this->startStage($document, 'ocr', $document->metadata['ocr_engine'] ?? null);

            try {
                $ocrResult = $this->ocrManager->extract($document, $ocrRecord->engine);
                $ocrRecord->update([
                    'status' => 'completed',
                    'ocr_text' => $ocrResult['text'] ?? null,
                    'metrics' => $ocrResult['metrics'] ?? null,
                    'payload' => $ocrResult['raw'] ?? null,
                    'completed_at' => now(),
                ]);
            } catch (\Throwable $e) {
                $this->failStage($ocrRecord, $e);

                throw $e;
            }

            $extractionRecord = $this->startStage($document, 'extraction', $document->metadata['llm_engine'] ?? 'gpt-4');

            try {
                $extraction = $this->extractionService->extract($document, $ocrRecord);
                $extractionRecord->update([
                    'status' => 'completed',
                    'payload' => $extraction,
                    'completed_at' => now(),
                ]);

                $matchingRecord = $this->startStage($document, 'matching');
                $matching = $this->matchingService->match($document, $extraction);
                $matchingRecord->update([
                    'status' => 'completed',
                    'payload' => $matching,
                    'completed_at' => now(),
                ]);

                $document->forceFill([
                    'extraction_payload' => $extraction,
                    'status' => Arr::get($matching, 'status', 'extracted'),
                    'processed_at' => now(),
                ])->save();
            } catch (\Throwable $e) {
                $this->failStage($extractionRecord, $e);

                throw $e;
            }
        });
    }

    private function startStage(Document $document, string $stage, ?string $engine = null): DocumentIngestion
    {
        return $document->ingestions()->create([
            'stage' => $stage,
            'status' => 'processing',
            'engine' => $engine,
            'started_at' => now(),
        ]);
    }

    private function failStage(DocumentIngestion $record, \Throwable $e): void
    {
        Log::error('Document stage failed', [
            'document_id' => $record->document_id,
            'stage' => $record->stage,
            'message' => $e->getMessage(),
        ]);

        $record->update([
            'status' => 'failed',
            'message' => Str::limit($e->getMessage(), 500),
            'completed_at' => now(),
        ]);
    }
}
