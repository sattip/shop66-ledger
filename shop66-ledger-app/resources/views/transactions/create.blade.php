@extends('layouts.app')

@section('page-title', isset($transaction) ? 'Edit Transaction' : 'Create Transaction')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('transactions.index') }}">Transactions</a></li>
    <li class="breadcrumb-item active">{{ isset($transaction) ? 'Edit' : 'Create' }}</li>
@endsection

@section('content')
    <form action="{{ isset($transaction) ? route('transactions.update', $transaction->id) : route('transactions.store') }}" method="POST">
        @csrf
        @if(isset($transaction))
            @method('PUT')
        @endif

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Transaction Details</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="date">Date <span class="text-danger">*</span></label>
                                    <input type="date" 
                                           class="form-control @error('date') is-invalid @enderror" 
                                           id="date" 
                                           name="date" 
                                           value="{{ old('date', isset($transaction) ? $transaction->date->format('Y-m-d') : date('Y-m-d')) }}" 
                                           required>
                                    @error('date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="reference">Reference</label>
                                    <input type="text" 
                                           class="form-control @error('reference') is-invalid @enderror" 
                                           id="reference" 
                                           name="reference" 
                                           value="{{ old('reference', $transaction->reference ?? '') }}" 
                                           placeholder="e.g., INV-001, CHK-123">
                                    @error('reference')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="type">Type <span class="text-danger">*</span></label>
                                    <select class="form-control @error('type') is-invalid @enderror" id="type" name="type" required>
                                        <option value="">Select Type</option>
                                        <option value="income" {{ old('type', $transaction->type ?? '') === 'income' ? 'selected' : '' }}>Income</option>
                                        <option value="expense" {{ old('type', $transaction->type ?? '') === 'expense' ? 'selected' : '' }}>Expense</option>
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="account_id">Account <span class="text-danger">*</span></label>
                                    <select class="form-control @error('account_id') is-invalid @enderror" id="account_id" name="account_id" required>
                                        <option value="">Select Account</option>
                                        @foreach($accounts ?? [] as $account)
                                            <option value="{{ $account->id }}" {{ old('account_id', $transaction->account_id ?? '') == $account->id ? 'selected' : '' }}>
                                                {{ $account->name }} ({{ $account->type }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('account_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Description <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="3" 
                                      required>{{ old('description', $transaction->description ?? '') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="vendor_id">Vendor/Customer</label>
                                    <select class="form-control @error('vendor_id') is-invalid @enderror" id="vendor_id" name="vendor_id">
                                        <option value="">Select Vendor/Customer</option>
                                        @foreach($vendors ?? [] as $vendor)
                                            <option value="{{ $vendor->id }}" {{ old('vendor_id', $transaction->vendor_id ?? '') == $vendor->id ? 'selected' : '' }}>
                                                {{ $vendor->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('vendor_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="amount">Amount <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">$</span>
                                        </div>
                                        <input type="number" 
                                               class="form-control @error('amount') is-invalid @enderror" 
                                               id="amount" 
                                               name="amount" 
                                               step="0.01" 
                                               min="0.01" 
                                               value="{{ old('amount', $transaction->amount ?? '') }}" 
                                               required>
                                        @error('amount')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
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
                        <div id="line-items">
                            @if(isset($transaction) && $transaction->lines->count() > 0)
                                @foreach($transaction->lines as $index => $line)
                                    <div class="row line-item mb-2" data-index="{{ $index }}">
                                        <div class="col-md-4">
                                            <select name="lines[{{ $index }}][item_id]" class="form-control">
                                                <option value="">Select Item</option>
                                                @foreach($items ?? [] as $item)
                                                    <option value="{{ $item->id }}" {{ $line->item_id == $item->id ? 'selected' : '' }}>
                                                        {{ $item->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="number" 
                                                   name="lines[{{ $index }}][quantity]" 
                                                   class="form-control" 
                                                   placeholder="Quantity" 
                                                   step="0.01" 
                                                   min="0.01" 
                                                   value="{{ $line->quantity }}">
                                        </div>
                                        <div class="col-md-2">
                                            <input type="number" 
                                                   name="lines[{{ $index }}][unit_price]" 
                                                   class="form-control" 
                                                   placeholder="Unit Price" 
                                                   step="0.01" 
                                                   min="0.01" 
                                                   value="{{ $line->unit_price }}">
                                        </div>
                                        <div class="col-md-3">
                                            <input type="text" 
                                                   name="lines[{{ $index }}][description]" 
                                                   class="form-control" 
                                                   placeholder="Description" 
                                                   value="{{ $line->description }}">
                                        </div>
                                        <div class="col-md-1">
                                            <button type="button" class="btn btn-danger btn-sm" onclick="removeLineItem(this)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-muted text-center py-3">
                                    No line items added. Click "Add Item" to add line items.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Status & Actions</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select class="form-control @error('status') is-invalid @enderror" id="status" name="status">
                                <option value="draft" {{ old('status', $transaction->status ?? 'draft') === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="pending" {{ old('status', $transaction->status ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="completed" {{ old('status', $transaction->status ?? '') === 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ old('status', $transaction->status ?? '') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="recurring" value="1" {{ old('recurring', $transaction->recurring ?? false) ? 'checked' : '' }}>
                                Recurring Transaction
                            </label>
                        </div>

                        <div class="form-group">
                            <label for="notes">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="4">{{ old('notes', $transaction->notes ?? '') }}</textarea>
                        </div>

                        <hr>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> {{ isset($transaction) ? 'Update' : 'Save' }} Transaction
                            </button>
                            <a href="{{ route('transactions.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
let lineItemIndex = {{ isset($transaction) ? $transaction->lines->count() : 0 }};

function addLineItem() {
    const lineItemsContainer = document.getElementById('line-items');
    const emptyMessage = lineItemsContainer.querySelector('.text-muted');
    if (emptyMessage) {
        emptyMessage.remove();
    }

    const lineItemHtml = `
        <div class="row line-item mb-2" data-index="${lineItemIndex}">
            <div class="col-md-4">
                <select name="lines[${lineItemIndex}][item_id]" class="form-control">
                    <option value="">Select Item</option>
                    @foreach($items ?? [] as $item)
                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <input type="number" 
                       name="lines[${lineItemIndex}][quantity]" 
                       class="form-control" 
                       placeholder="Quantity" 
                       step="0.01" 
                       min="0.01">
            </div>
            <div class="col-md-2">
                <input type="number" 
                       name="lines[${lineItemIndex}][unit_price]" 
                       class="form-control" 
                       placeholder="Unit Price" 
                       step="0.01" 
                       min="0.01">
            </div>
            <div class="col-md-3">
                <input type="text" 
                       name="lines[${lineItemIndex}][description]" 
                       class="form-control" 
                       placeholder="Description">
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-danger btn-sm" onclick="removeLineItem(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;

    lineItemsContainer.insertAdjacentHTML('beforeend', lineItemHtml);
    lineItemIndex++;
}

function removeLineItem(button) {
    const lineItem = button.closest('.line-item');
    lineItem.remove();

    const lineItemsContainer = document.getElementById('line-items');
    if (lineItemsContainer.children.length === 0) {
        lineItemsContainer.innerHTML = '<div class="text-muted text-center py-3">No line items added. Click "Add Item" to add line items.</div>';
    }
}
</script>
@endpush