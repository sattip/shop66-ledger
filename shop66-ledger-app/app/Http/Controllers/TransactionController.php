<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Account;
use App\Models\Vendor;
use App\Models\Item;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class TransactionController extends Controller
{
    public function index()
    {
        $accounts = Account::orderBy('name')->get();
        return view('transactions.index', compact('accounts'));
    }

    public function datatables(Request $request)
    {
        $query = Transaction::with(['account', 'vendor']);

        // Apply filters
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('account_id')) {
            $query->where('account_id', $request->account_id);
        }

        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        return DataTables::eloquent($query)
            ->addColumn('actions', function ($transaction) {
                return view('transactions.actions', compact('transaction'))->render();
            })
            ->editColumn('date', function ($transaction) {
                return $transaction->date->format('M d, Y');
            })
            ->editColumn('amount', function ($transaction) {
                return number_format($transaction->amount, 2);
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    public function create()
    {
        $accounts = Account::orderBy('name')->get();
        $vendors = Vendor::orderBy('name')->get();
        $items = Item::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();

        return view('transactions.create', compact('accounts', 'vendors', 'items', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'type' => 'required|in:income,expense',
            'account_id' => 'required|exists:accounts,id',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'status' => 'in:draft,pending,completed,cancelled',
        ]);

        DB::transaction(function () use ($request) {
            $transaction = Transaction::create([
                'date' => $request->date,
                'reference' => $request->reference,
                'type' => $request->type,
                'account_id' => $request->account_id,
                'vendor_id' => $request->vendor_id,
                'description' => $request->description,
                'amount' => $request->amount,
                'status' => $request->status ?? 'draft',
                'recurring' => $request->boolean('recurring'),
                'notes' => $request->notes,
                'store_id' => session('current_store_id'),
            ]);

            // Process line items
            if ($request->has('lines')) {
                foreach ($request->lines as $line) {
                    if (!empty($line['description']) || !empty($line['item_id'])) {
                        $transaction->lines()->create([
                            'item_id' => $line['item_id'] ?? null,
                            'description' => $line['description'] ?? '',
                            'quantity' => $line['quantity'] ?? 1,
                            'unit_price' => $line['unit_price'] ?? 0,
                            'total' => ($line['quantity'] ?? 1) * ($line['unit_price'] ?? 0),
                        ]);
                    }
                }
            }
        });

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction created successfully.');
    }

    public function show(Transaction $transaction)
    {
        $transaction->load(['account', 'vendor', 'lines.item', 'lines.category']);
        return view('transactions.show', compact('transaction'));
    }

    public function edit(Transaction $transaction)
    {
        $accounts = Account::orderBy('name')->get();
        $vendors = Vendor::orderBy('name')->get();
        $items = Item::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();

        $transaction->load('lines');

        return view('transactions.create', compact('transaction', 'accounts', 'vendors', 'items', 'categories'));
    }

    public function update(Request $request, Transaction $transaction)
    {
        $request->validate([
            'date' => 'required|date',
            'type' => 'required|in:income,expense',
            'account_id' => 'required|exists:accounts,id',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'status' => 'in:draft,pending,completed,cancelled',
        ]);

        DB::transaction(function () use ($request, $transaction) {
            $transaction->update([
                'date' => $request->date,
                'reference' => $request->reference,
                'type' => $request->type,
                'account_id' => $request->account_id,
                'vendor_id' => $request->vendor_id,
                'description' => $request->description,
                'amount' => $request->amount,
                'status' => $request->status ?? 'draft',
                'recurring' => $request->boolean('recurring'),
                'notes' => $request->notes,
            ]);

            // Delete existing line items and recreate
            $transaction->lines()->delete();

            if ($request->has('lines')) {
                foreach ($request->lines as $line) {
                    if (!empty($line['description']) || !empty($line['item_id'])) {
                        $transaction->lines()->create([
                            'item_id' => $line['item_id'] ?? null,
                            'description' => $line['description'] ?? '',
                            'quantity' => $line['quantity'] ?? 1,
                            'unit_price' => $line['unit_price'] ?? 0,
                            'total' => ($line['quantity'] ?? 1) * ($line['unit_price'] ?? 0),
                        ]);
                    }
                }
            }
        });

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction updated successfully.');
    }

    public function destroy(Transaction $transaction)
    {
        $transaction->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction deleted successfully.');
    }
}