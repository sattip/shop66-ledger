@extends('layouts.app')

@section('page-title', 'Transactions')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Transactions</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Transactions</h3>
            <div class="card-tools">
                <a href="{{ route('transactions.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Transaction
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Filters -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="type_filter">Type</label>
                    <select id="type_filter" class="form-control">
                        <option value="">All Types</option>
                        <option value="income">Income</option>
                        <option value="expense">Expense</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="account_filter">Account</label>
                    <select id="account_filter" class="form-control">
                        <option value="">All Accounts</option>
                        @foreach($accounts ?? [] as $account)
                            <option value="{{ $account->id }}">{{ $account->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="date_from">Date From</label>
                    <input type="date" id="date_from" class="form-control">
                </div>
                <div class="col-md-3">
                    <label for="date_to">Date To</label>
                    <input type="date" id="date_to" class="form-control">
                </div>
            </div>

            <div class="table-responsive">
                <table id="transactions-table" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Reference</th>
                            <th>Description</th>
                            <th>Account</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be loaded via DataTables AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    var table = $('#transactions-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('transactions.datatables') }}",
            data: function(d) {
                d.type = $('#type_filter').val();
                d.account_id = $('#account_filter').val();
                d.date_from = $('#date_from').val();
                d.date_to = $('#date_to').val();
            }
        },
        columns: [
            { data: 'date', name: 'date' },
            { data: 'reference', name: 'reference' },
            { data: 'description', name: 'description' },
            { data: 'account.name', name: 'account.name' },
            { 
                data: 'type', 
                name: 'type',
                render: function(data) {
                    var badgeClass = data === 'income' ? 'success' : 'danger';
                    return '<span class="badge badge-' + badgeClass + '">' + data.charAt(0).toUpperCase() + data.slice(1) + '</span>';
                }
            },
            { 
                data: 'amount', 
                name: 'amount',
                render: function(data) {
                    return '$' + parseFloat(data).toLocaleString('en-US', {minimumFractionDigits: 2});
                }
            },
            { 
                data: 'status', 
                name: 'status',
                render: function(data) {
                    var badgeClass = data === 'completed' ? 'success' : (data === 'pending' ? 'warning' : 'secondary');
                    return '<span class="badge badge-' + badgeClass + '">' + data.charAt(0).toUpperCase() + data.slice(1) + '</span>';
                }
            },
            { 
                data: 'actions', 
                name: 'actions', 
                orderable: false, 
                searchable: false,
                render: function(data, type, row) {
                    return `
                        <div class="btn-group" role="group">
                            <a href="/transactions/${row.id}" class="btn btn-sm btn-info" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="/transactions/${row.id}/edit" class="btn btn-sm btn-warning" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteTransaction(${row.id})" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ],
        order: [[0, 'desc']],
        pageLength: 25,
        responsive: true,
        stateSave: true
    });

    // Refresh table when filters change
    $('#type_filter, #account_filter, #date_from, #date_to').on('change', function() {
        table.draw();
    });
});

function deleteTransaction(id) {
    if (confirm('Are you sure you want to delete this transaction?')) {
        $.ajax({
            url: '/transactions/' + id,
            type: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function() {
                $('#transactions-table').DataTable().ajax.reload();
                alert('Transaction deleted successfully');
            },
            error: function() {
                alert('Error deleting transaction');
            }
        });
    }
}
</script>
@endpush