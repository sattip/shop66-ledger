<?php

namespace App\Services\Documents;

use App\Models\Document;
use App\Models\DocumentIngestion;
use Illuminate\Support\Str;

class ExtractionService
{
    public function extract(Document $document, DocumentIngestion $ocrRecord): array
    {
        $text = $ocrRecord->ocr_text ?? '';
        $lines = preg_split('/\r?\n/', (string) $text) ?: [];

        $totals = $this->guessTotals($lines);

        return [
            'document_number' => $this->guessDocumentNumber($lines) ?? $document->document_number,
            'document_date' => $this->guessDate($lines),
            'due_date' => $this->guessDueDate($lines),
            'vendor' => $this->guessVendor($lines),
            'totals' => $totals,
            'line_items' => $this->guessLineItems($lines, $totals),
        ];
    }

    private function guessDocumentNumber(array $lines): ?string
    {
        foreach ($lines as $line) {
            if (Str::contains(strtolower($line), 'invoice')) {
                preg_match('/(\d{3,})/', $line, $matches);

                return $matches[1] ?? null;
            }
        }

        return null;
    }

    private function guessDate(array $lines): ?string
    {
        foreach ($lines as $line) {
            if (preg_match('/(\d{4}-\d{2}-\d{2})/', $line, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    private function guessDueDate(array $lines): ?string
    {
        foreach ($lines as $line) {
            if (Str::contains(strtolower($line), 'due') && preg_match('/(\d{4}-\d{2}-\d{2})/', $line, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    private function guessVendor(array $lines): ?array
    {
        $firstLine = trim($lines[0] ?? '');

        return $firstLine ? ['name' => $firstLine] : null;
    }

    private function guessTotals(array $lines): array
    {
        $result = [
            'subtotal' => null,
            'tax_total' => null,
            'total' => null,
        ];

        foreach ($lines as $line) {
            if ($this->matchesAmount($line, ['subtotal', 'sub total'])) {
                $result['subtotal'] = $this->extractAmount($line);
            }
            if ($this->matchesAmount($line, ['tax'])) {
                $result['tax_total'] = $this->extractAmount($line);
            }
            if ($this->matchesAmount($line, ['total'])) {
                $result['total'] = $this->extractAmount($line);
            }
        }

        return $result;
    }

    private function guessLineItems(array $lines, array $totals): array
    {
        $items = [];

        foreach ($lines as $line) {
            if (preg_match('/(\d+(?:\.\d{1,2})?)\s+x\s+(.*)\s+@(\d+(?:\.\d{1,2})?)/i', $line, $matches)) {
                $quantity = (float) $matches[1];
                $description = trim($matches[2]);
                $unitPrice = (float) $matches[3];
                $lineTotal = $quantity * $unitPrice;

                $items[] = [
                    'description' => $description,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total' => $lineTotal,
                ];
            }
        }

        return $items ?: [[
            'description' => 'Document total',
            'quantity' => 1,
            'unit_price' => (float) ($totals['total'] ?? 0),
            'total' => (float) ($totals['total'] ?? 0),
        ]];
    }

    private function matchesAmount(string $line, array $keywords): bool
    {
        $lower = strtolower($line);

        foreach ($keywords as $keyword) {
            if (Str::contains($lower, $keyword)) {
                return true;
            }
        }

        return false;
    }

    private function extractAmount(string $line): ?float
    {
        if (preg_match('/(\d+[\.,]\d{2})/', $line, $matches)) {
            return (float) str_replace(',', '', $matches[1]);
        }

        return null;
    }
}
