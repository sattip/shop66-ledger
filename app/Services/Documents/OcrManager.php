<?php

namespace App\Services\Documents;

use App\Models\Document;
use Illuminate\Support\Facades\Storage;

class OcrManager
{
    public function extract(Document $document, ?string $engine = null): array
    {
        $engine = $engine ?? 'tesseract';

        $path = Storage::disk($document->disk)->path($document->path);
        $text = $this->readFile($path);

        return [
            'engine' => $engine,
            'text' => $text,
            'metrics' => [
                'characters' => mb_strlen($text),
                'engine' => $engine,
            ],
        ];
    }

    private function readFile(string $path): string
    {
        $contents = @file_get_contents($path);

        if (! $contents) {
            return '';
        }

        // Attempt to detect binary PDFs and return placeholder text.
        if (! mb_detect_encoding($contents, mb_list_encodings(), true)) {
            return '[binary document content]';
        }

        return $contents;
    }
}
