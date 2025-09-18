<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Vendor;
use App\Models\Item;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Jobs\ProcessDocumentJob;

class DocumentController extends Controller
{
    public function index()
    {
        $documents = Document::with(['store'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('documents.index', compact('documents'));
    }

    public function upload()
    {
        return view('documents.upload');
    }

    public function store(Request $request)
    {
        $request->validate([
            'documents' => 'required|array',
            'documents.*' => 'file|mimes:pdf,jpg,jpeg,png,webp|max:10240', // 10MB
            'document_type' => 'required|string',
            'priority' => 'required|in:normal,high,urgent',
        ]);

        $uploadedDocuments = [];

        foreach ($request->file('documents') as $file) {
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('documents', $filename, 'local');

            $document = Document::create([
                'filename' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'document_type' => $request->document_type,
                'priority' => $request->priority,
                'status' => 'uploaded',
                'notes' => $request->notes,
                'store_id' => session('current_store_id'),
                'uploaded_by' => auth()->id(),
            ]);

            $uploadedDocuments[] = $document;

            // Queue document processing if auto_process is enabled
            if ($request->boolean('auto_process')) {
                ProcessDocumentJob::dispatch($document);
                $document->update(['status' => 'processing']);
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => count($uploadedDocuments) . ' documents uploaded successfully',
                'documents' => $uploadedDocuments,
                'redirect' => route('documents.index')
            ]);
        }

        return redirect()->route('documents.index')
            ->with('success', count($uploadedDocuments) . ' documents uploaded successfully');
    }

    public function review($id = null)
    {
        if ($id) {
            $document = Document::findOrFail($id);
            
            // Load related data for the review form
            $vendors = Vendor::orderBy('name')->get();
            $items = Item::orderBy('name')->get();
            $categories = Category::orderBy('name')->get();
            
            // Get extracted data (this would come from the AI extraction)
            $extractedData = $this->getExtractedData($document);
            
            return view('documents.review', compact('document', 'vendors', 'items', 'categories', 'extractedData'));
        }

        // Show list of documents pending review
        $documents = Document::where('status', 'pending_review')
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'asc')
            ->paginate(20);

        return view('documents.pending-review', compact('documents'));
    }

    public function approve(Request $request, Document $document)
    {
        $request->validate([
            'action' => 'required|in:approve,save_draft',
            'document_type' => 'required',
            'document_date' => 'required|date',
            'vendor_id' => 'required|exists:vendors,id',
            'total_amount' => 'required|numeric|min:0',
        ]);

        if ($request->action === 'approve') {
            // Create transaction from approved document
            $transaction = $this->createTransactionFromDocument($document, $request);
            
            $document->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'transaction_id' => $transaction->id,
            ]);

            return redirect()->route('documents.index')
                ->with('success', 'Document approved and transaction created successfully');
        } else {
            // Save as draft
            $document->update([
                'status' => 'draft',
                'extracted_data' => $request->except(['_token', 'action']),
            ]);

            return redirect()->route('documents.review', $document->id)
                ->with('success', 'Document saved as draft');
        }
    }

    public function reject(Request $request, Document $document)
    {
        $document->update([
            'status' => 'rejected',
            'rejected_by' => auth()->id(),
            'rejected_at' => now(),
            'rejection_reason' => $request->notes,
        ]);

        return redirect()->route('documents.index')
            ->with('success', 'Document rejected');
    }

    public function reprocess(Document $document)
    {
        $document->update([
            'status' => 'processing',
            'ocr_text' => null,
            'extracted_data' => null,
        ]);

        ProcessDocumentJob::dispatch($document);

        return redirect()->route('documents.review', $document->id)
            ->with('success', 'Document queued for reprocessing');
    }

    private function getExtractedData(Document $document)
    {
        // This would typically come from the document's extracted_data field
        // For demo purposes, return sample data
        return [
            'document_type' => $document->document_type ?? 'invoice',
            'date' => $document->created_at->format('Y-m-d'),
            'date_confidence' => 0.95,
            'vendor_name' => 'Sample Vendor',
            'vendor_confidence' => 0.85,
            'matched_vendor_id' => null,
            'reference' => 'INV-' . rand(1000, 9999),
            'reference_confidence' => 0.90,
            'subtotal' => 100.00,
            'subtotal_confidence' => 0.95,
            'tax_amount' => 10.00,
            'tax_confidence' => 0.90,
            'total' => 110.00,
            'total_confidence' => 0.95,
            'calculated_total' => 110.00,
            'line_items' => [
                [
                    'description' => 'Sample Item 1',
                    'quantity' => 2,
                    'unit_price' => 25.00,
                    'total' => 50.00,
                    'confidence' => 0.85,
                    'matched_item_id' => null,
                    'matched_category_id' => null,
                ],
                [
                    'description' => 'Sample Item 2',
                    'quantity' => 1,
                    'unit_price' => 50.00,
                    'total' => 50.00,
                    'confidence' => 0.90,
                    'matched_item_id' => null,
                    'matched_category_id' => null,
                ]
            ]
        ];
    }

    private function createTransactionFromDocument(Document $document, Request $request)
    {
        // This would create a transaction based on the approved document data
        // Implementation would depend on your Transaction model structure
        
        return \App\Models\Transaction::create([
            'date' => $request->document_date,
            'reference' => $request->reference,
            'type' => 'expense', // Assuming most documents are expenses
            'description' => 'Document: ' . $document->filename,
            'amount' => $request->total_amount,
            'vendor_id' => $request->vendor_id,
            'status' => 'completed',
            'document_id' => $document->id,
            'store_id' => $document->store_id,
        ]);
    }
}