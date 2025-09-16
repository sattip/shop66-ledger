<?php

namespace App\Jobs;

use App\Models\Document;
use App\Services\Documents\DocumentProcessingPipeline;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessDocumentJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(private readonly Document $document)
    {
        $this->onQueue('documents');
    }

    public function handle(DocumentProcessingPipeline $pipeline): void
    {
        $pipeline->process($this->document->fresh());
    }
}
