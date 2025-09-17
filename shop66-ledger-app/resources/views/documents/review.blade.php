@extends('layouts.app')

@section('page-title', 'Review Document')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('documents.index') }}">Documents</a></li>
    <li class="breadcrumb-item active">Review</li>
@endsection

@push('styles')
<style>
.document-viewer {
    height: 80vh;
    border: 1px solid #dee2e6;
    border-radius: 5px;
    overflow: hidden;
}

.document-iframe {
    width: 100%;
    height: 100%;
    border: none;
}

.confidence-indicator {
    display: inline-block;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    margin-left: 5px;
}

.confidence-high { background-color: #28a745; }
.confidence-medium { background-color: #ffc107; }
.confidence-low { background-color: #dc3545; }

.field-group {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 5px;
    padding: 15px;
    margin-bottom: 15px;
}

.extraction-status {
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 20px;
}

.status-processing { background: #d4edda; color: #155724; }
.status-completed { background: #cce5ff; color: #004085; }
.status-error { background: #f8d7da; color: #721c24; }

.suggested-match {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 3px;
    padding: 8px;
    margin-top: 5px;
}

.line-item-row {
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 5px;
    padding: 10px;
    margin-bottom: 10px;
}

.validation-error {
    border-color: #dc3545 !important;
    background-color: #f8d7da;
}

.validation-warning {
    border-color: #ffc107 !important;
    background-color: #fff3cd;
}
</style>
@endpush

@section('content')
    <div class="row">
        <!-- Document Viewer -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-file-alt"></i> {{ $document->filename ?? 'Document Preview' }}
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-{{ $document->status === 'completed' ? 'success' : 'warning' }}">
                            {{ ucfirst($document->status ?? 'processing') }}
                        </span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="document-viewer">
                        @if(isset($document) && $document->file_path)
                            @if(str_ends_with($document->file_path, '.pdf'))
                                <iframe src="{{ Storage::url($document->file_path) }}" class="document-iframe"></iframe>
                            @else
                                <img src="{{ Storage::url($document->file_path) }}" alt="Document" style="width: 100%; height: 100%; object-fit: contain;">
                            @endif
                        @else
                            <div class="d-flex align-items-center justify-content-center h-100 text-muted">
                                <div class="text-center">
                                    <i class="fas fa-file-alt fa-3x mb-3"></i>
                                    <p>Document preview not available</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- OCR Text -->
            @if(isset($document) && $document->ocr_text)
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-text-width"></i> Extracted Text (OCR)
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div style="max-height: 300px; overflow-y: auto; font-family: monospace; font-size: 12px; background: #f8f9fa; padding: 10px; border-radius: 3px;">
                        {{ $document->ocr_text }}
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Extracted Data Form -->
        <div class="col-md-6">
            <form id="reviewForm" action="{{ route('documents.approve', $document->id ?? '') }}" method="POST">
                @csrf
                
                <!-- Extraction Status -->
                <div class="extraction-status status-{{ $document->status ?? 'processing' }}">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-{{ $document->status === 'completed' ? 'check-circle' : 'spinner fa-spin' }} mr-2"></i>
                        <div>
                            <strong>Extraction Status:</strong> {{ ucfirst($document->status ?? 'Processing') }}
                            @if(isset($document) && $document->confidence_score)
                                <br><small>Overall Confidence: {{ number_format($document->confidence_score * 100, 1) }}%</small>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Document Header Information -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Document Information</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Document Type 
                                        <span class="confidence-indicator confidence-high" title="High confidence"></span>
                                    </label>
                                    <select class="form-control" name="document_type">
                                        <option value="invoice" {{ ($extractedData['document_type'] ?? '') === 'invoice' ? 'selected' : '' }}>Invoice</option>
                                        <option value="receipt" {{ ($extractedData['document_type'] ?? '') === 'receipt' ? 'selected' : '' }}>Receipt</option>
                                        <option value="purchase_order" {{ ($extractedData['document_type'] ?? '') === 'purchase_order' ? 'selected' : '' }}>Purchase Order</option>
                                        <option value="expense_report" {{ ($extractedData['document_type'] ?? '') === 'expense_report' ? 'selected' : '' }}>Expense Report</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Document Date 
                                        <span class="confidence-indicator confidence-{{ ($extractedData['date_confidence'] ?? 0) > 0.8 ? 'high' : (($extractedData['date_confidence'] ?? 0) > 0.5 ? 'medium' : 'low') }}" 
                                               title="Confidence: {{ number_format(($extractedData['date_confidence'] ?? 0) * 100, 1) }}%"></span>
                                    </label>
                                    <input type="date" 
                                           class="form-control {{ ($extractedData['date_confidence'] ?? 0) < 0.5 ? 'validation-warning' : '' }}" 
                                           name="document_date" 
                                           value="{{ $extractedData['date'] ?? '' }}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>Vendor 
                                        <span class="confidence-indicator confidence-{{ ($extractedData['vendor_confidence'] ?? 0) > 0.8 ? 'high' : (($extractedData['vendor_confidence'] ?? 0) > 0.5 ? 'medium' : 'low') }}" 
                                               title="Confidence: {{ number_format(($extractedData['vendor_confidence'] ?? 0) * 100, 1) }}%"></span>
                                    </label>
                                    <select class="form-control {{ ($extractedData['vendor_confidence'] ?? 0) < 0.5 ? 'validation-warning' : '' }}" 
                                            name="vendor_id" id="vendor_select">
                                        <option value="">Select Vendor</option>
                                        @foreach($vendors ?? [] as $vendor)
                                            <option value="{{ $vendor->id }}" 
                                                    {{ ($extractedData['matched_vendor_id'] ?? '') == $vendor->id ? 'selected' : '' }}>
                                                {{ $vendor->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if(isset($extractedData['vendor_name']) && !isset($extractedData['matched_vendor_id']))
                                        <div class="suggested-match">
                                            <small><strong>Extracted:</strong> "{{ $extractedData['vendor_name'] }}"</small>
                                            <button type="button" class="btn btn-xs btn-outline-primary ml-2" onclick="createNewVendor()">
                                                Create New Vendor
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Reference # 
                                        <span class="confidence-indicator confidence-{{ ($extractedData['reference_confidence'] ?? 0) > 0.8 ? 'high' : (($extractedData['reference_confidence'] ?? 0) > 0.5 ? 'medium' : 'low') }}" 
                                               title="Confidence: {{ number_format(($extractedData['reference_confidence'] ?? 0) * 100, 1) }}%"></span>
                                    </label>
                                    <input type="text" 
                                           class="form-control {{ ($extractedData['reference_confidence'] ?? 0) < 0.5 ? 'validation-warning' : '' }}" 
                                           name="reference" 
                                           value="{{ $extractedData['reference'] ?? '' }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Line Items -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Line Items</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-sm btn-primary" onclick="addLineItem()">
                                <i class="fas fa-plus"></i> Add Item
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="line-items-container">
                            @if(isset($extractedData['line_items']) && count($extractedData['line_items']) > 0)
                                @foreach($extractedData['line_items'] as $index => $item)
                                    <div class="line-item-row" data-index="{{ $index }}">
                                        <div class="row">
                                            <div class="col-md-5">
                                                <label>Item/Description</label>
                                                <input type="text" 
                                                       class="form-control" 
                                                       name="items[{{ $index }}][description]" 
                                                       value="{{ $item['description'] ?? '' }}">
                                                @if(isset($item['matched_item_id']))
                                                    <select class="form-control mt-1" name="items[{{ $index }}][item_id]">
                                                        <option value="">Select Item</option>
                                                        @foreach($items ?? [] as $dbItem)
                                                            <option value="{{ $dbItem->id }}" 
                                                                    {{ $item['matched_item_id'] == $dbItem->id ? 'selected' : '' }}>
                                                                {{ $dbItem->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                @endif
                                            </div>
                                            <div class="col-md-2">
                                                <label>Quantity</label>
                                                <input type="number" 
                                                       class="form-control" 
                                                       name="items[{{ $index }}][quantity]" 
                                                       value="{{ $item['quantity'] ?? 1 }}" 
                                                       step="0.01" 
                                                       min="0.01">
                                            </div>
                                            <div class="col-md-2">
                                                <label>Unit Price</label>
                                                <input type="number" 
                                                       class="form-control" 
                                                       name="items[{{ $index }}][unit_price]" 
                                                       value="{{ $item['unit_price'] ?? 0 }}" 
                                                       step="0.01" 
                                                       min="0">
                                            </div>
                                            <div class="col-md-2">
                                                <label>Total</label>
                                                <input type="number" 
                                                       class="form-control" 
                                                       name="items[{{ $index }}][total]" 
                                                       value="{{ $item['total'] ?? 0 }}" 
                                                       step="0.01" 
                                                       readonly>
                                            </div>
                                            <div class="col-md-1">
                                                <label>&nbsp;</label>
                                                <button type="button" class="btn btn-danger btn-sm d-block" onclick="removeLineItem(this)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-md-6">
                                                <select class="form-control" name="items[{{ $index }}][category_id]">
                                                    <option value="">Select Category</option>
                                                    @foreach($categories ?? [] as $category)
                                                        <option value="{{ $category->id }}" 
                                                                {{ ($item['matched_category_id'] ?? '') == $category->id ? 'selected' : '' }}>
                                                            {{ $category->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="text-muted small">
                                                    Confidence: {{ number_format(($item['confidence'] ?? 0) * 100, 1) }}%
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-center text-muted py-4">
                                    <i class="fas fa-list"></i>
                                    <p>No line items extracted. Add items manually or re-process document.</p>
                                </div>
                            @endif
                        </div>

                        <!-- Totals -->
                        <div class="field-group mt-4">
                            <div class="row">
                                <div class="col-md-4">
                                    <label>Subtotal 
                                        <span class="confidence-indicator confidence-{{ ($extractedData['subtotal_confidence'] ?? 0) > 0.8 ? 'high' : (($extractedData['subtotal_confidence'] ?? 0) > 0.5 ? 'medium' : 'low') }}" 
                                               title="Confidence: {{ number_format(($extractedData['subtotal_confidence'] ?? 0) * 100, 1) }}%"></span>
                                    </label>
                                    <input type="number" 
                                           class="form-control" 
                                           name="subtotal" 
                                           value="{{ $extractedData['subtotal'] ?? 0 }}" 
                                           step="0.01" 
                                           readonly>
                                </div>
                                <div class="col-md-4">
                                    <label>Tax Amount 
                                        <span class="confidence-indicator confidence-{{ ($extractedData['tax_confidence'] ?? 0) > 0.8 ? 'high' : (($extractedData['tax_confidence'] ?? 0) > 0.5 ? 'medium' : 'low') }}" 
                                               title="Confidence: {{ number_format(($extractedData['tax_confidence'] ?? 0) * 100, 1) }}%"></span>
                                    </label>
                                    <input type="number" 
                                           class="form-control" 
                                           name="tax_amount" 
                                           value="{{ $extractedData['tax_amount'] ?? 0 }}" 
                                           step="0.01">
                                </div>
                                <div class="col-md-4">
                                    <label>Total Amount 
                                        <span class="confidence-indicator confidence-{{ ($extractedData['total_confidence'] ?? 0) > 0.8 ? 'high' : (($extractedData['total_confidence'] ?? 0) > 0.5 ? 'medium' : 'low') }}" 
                                               title="Confidence: {{ number_format(($extractedData['total_confidence'] ?? 0) * 100, 1) }}%"></span>
                                    </label>
                                    <input type="number" 
                                           class="form-control {{ abs(($extractedData['calculated_total'] ?? 0) - ($extractedData['total'] ?? 0)) > 0.01 ? 'validation-error' : '' }}" 
                                           name="total_amount" 
                                           value="{{ $extractedData['total'] ?? 0 }}" 
                                           step="0.01">
                                    @if(isset($extractedData['calculated_total']) && abs($extractedData['calculated_total'] - ($extractedData['total'] ?? 0)) > 0.01)
                                        <small class="text-danger">
                                            ⚠ Calculated: ${{ number_format($extractedData['calculated_total'], 2) }}
                                        </small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="notes">Review Notes</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Add any notes about this review..."></textarea>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label>Actions</label>
                                <div class="d-grid gap-2">
                                    <button type="submit" name="action" value="approve" class="btn btn-success">
                                        <i class="fas fa-check"></i> Approve & Post
                                    </button>
                                    <button type="submit" name="action" value="save_draft" class="btn btn-warning">
                                        <i class="fas fa-save"></i> Save as Draft
                                    </button>
                                    <button type="button" class="btn btn-danger" onclick="rejectDocument()">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="reprocessDocument()">
                                        <i class="fas fa-redo"></i> Re-process
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
let lineItemIndex = {{ count($extractedData['line_items'] ?? []) }};

$(document).ready(function() {
    calculateTotals();
    setupFormValidation();
});

function addLineItem() {
    const container = document.getElementById('line-items-container');
    const emptyMessage = container.querySelector('.text-center');
    if (emptyMessage) {
        emptyMessage.remove();
    }

    const newItemHtml = `
        <div class="line-item-row" data-index="${lineItemIndex}">
            <div class="row">
                <div class="col-md-5">
                    <label>Item/Description</label>
                    <input type="text" class="form-control" name="items[${lineItemIndex}][description]">
                    <select class="form-control mt-1" name="items[${lineItemIndex}][item_id]">
                        <option value="">Select Item</option>
                        @foreach($items ?? [] as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Quantity</label>
                    <input type="number" class="form-control" name="items[${lineItemIndex}][quantity]" value="1" step="0.01" min="0.01" onchange="calculateLineTotal(this)">
                </div>
                <div class="col-md-2">
                    <label>Unit Price</label>
                    <input type="number" class="form-control" name="items[${lineItemIndex}][unit_price]" value="0" step="0.01" min="0" onchange="calculateLineTotal(this)">
                </div>
                <div class="col-md-2">
                    <label>Total</label>
                    <input type="number" class="form-control" name="items[${lineItemIndex}][total]" value="0" step="0.01" readonly>
                </div>
                <div class="col-md-1">
                    <label>&nbsp;</label>
                    <button type="button" class="btn btn-danger btn-sm d-block" onclick="removeLineItem(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-6">
                    <select class="form-control" name="items[${lineItemIndex}][category_id]">
                        <option value="">Select Category</option>
                        @foreach($categories ?? [] as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', newItemHtml);
    lineItemIndex++;
}

function removeLineItem(button) {
    button.closest('.line-item-row').remove();
    calculateTotals();
}

function calculateLineTotal(input) {
    const row = input.closest('.line-item-row');
    const quantity = parseFloat(row.querySelector('input[name*="[quantity]"]').value) || 0;
    const unitPrice = parseFloat(row.querySelector('input[name*="[unit_price]"]').value) || 0;
    const total = quantity * unitPrice;
    
    row.querySelector('input[name*="[total]"]').value = total.toFixed(2);
    calculateTotals();
}

function calculateTotals() {
    let subtotal = 0;
    document.querySelectorAll('input[name*="[total]"]').forEach(input => {
        subtotal += parseFloat(input.value) || 0;
    });

    const taxAmount = parseFloat(document.querySelector('input[name="tax_amount"]').value) || 0;
    const total = subtotal + taxAmount;

    document.querySelector('input[name="subtotal"]').value = subtotal.toFixed(2);
    document.querySelector('input[name="total_amount"]').value = total.toFixed(2);
}

function setupFormValidation() {
    document.getElementById('reviewForm').addEventListener('submit', function(e) {
        if (!validateForm()) {
            e.preventDefault();
        }
    });
}

function validateForm() {
    let isValid = true;
    const errors = [];

    // Check if vendor is selected
    if (!document.querySelector('select[name="vendor_id"]').value) {
        errors.push('Please select a vendor');
        isValid = false;
    }

    // Check if total amounts make sense
    const subtotal = parseFloat(document.querySelector('input[name="subtotal"]').value) || 0;
    const taxAmount = parseFloat(document.querySelector('input[name="tax_amount"]').value) || 0;
    const totalAmount = parseFloat(document.querySelector('input[name="total_amount"]').value) || 0;

    if (Math.abs((subtotal + taxAmount) - totalAmount) > 0.01) {
        errors.push('Total amount does not match subtotal + tax');
        isValid = false;
    }

    if (errors.length > 0) {
        alert('Please fix the following errors:\n- ' + errors.join('\n- '));
    }

    return isValid;
}

function createNewVendor() {
    const vendorName = '{{ $extractedData["vendor_name"] ?? "" }}';
    if (confirm(`Create new vendor "${vendorName}"?`)) {
        // This would typically open a modal or redirect to vendor creation
        // For now, just add it to the select
        const select = document.getElementById('vendor_select');
        const option = new Option(vendorName, 'new_' + vendorName);
        select.add(option);
        select.value = option.value;
    }
}

function rejectDocument() {
    if (confirm('Are you sure you want to reject this document? This action cannot be undone.')) {
        const form = document.getElementById('reviewForm');
        form.action = '{{ route("documents.reject", $document->id ?? "") }}';
        form.submit();
    }
}

function reprocessDocument() {
    if (confirm('Re-process this document? This will restart the OCR and AI extraction.')) {
        window.location.href = '{{ route("documents.reprocess", $document->id ?? "") }}';
    }
}

// Auto-calculate line totals when quantity or unit price changes
document.addEventListener('change', function(e) {
    if (e.target.name && (e.target.name.includes('[quantity]') || e.target.name.includes('[unit_price]'))) {
        calculateLineTotal(e.target);
    }
    if (e.target.name === 'tax_amount') {
        calculateTotals();
    }
});
</script>
@endpush