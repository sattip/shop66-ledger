<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use App\Models\Store;
use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\Widget;

class AnalyticsFiltersWidget extends Widget
{
    protected static string $view = 'filament.widgets.analytics-filters-widget';

    protected static ?int $sort = -1;

    protected int|string|array $columnSpan = 'full';

    protected $listeners = ['store-changed' => '$refresh'];

    public array $filters = [
        'date_from' => '',
        'date_to' => '',
        'store_id' => 'all',
        'type' => 'all',
        'category_id' => 'all',
    ];

    public function mount(): void
    {
        $this->filters = [
            'date_from' => Carbon::now()->startOfMonth()->format('Y-m-d'),
            'date_to' => Carbon::now()->format('Y-m-d'),
            'store_id' => app(\App\Support\StoreContext::class)->get() ?? 'all',
            'type' => 'all',
            'category_id' => 'all',
        ];
    }

    public function updatedFiltersStoreId($value): void
    {
        // When store filter changes, update the store context and refresh charts
        if ($value !== 'all') {
            app(\App\Support\StoreContext::class)->set((int) $value);
            app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId((int) $value);
        } else {
            app(\App\Support\StoreContext::class)->clear();
        }

        $this->dispatch('store-changed');
        $this->dispatch('filters-updated', $this->filters);
    }

    public function applyFilters(): void
    {
        $this->dispatch('filters-updated', $this->filters);
    }

    public function resetFilters(): void
    {
        $this->filters = [
            'date_from' => Carbon::now()->startOfMonth()->format('Y-m-d'),
            'date_to' => Carbon::now()->format('Y-m-d'),
            'store_id' => 'all',
            'type' => 'all',
            'category_id' => 'all',
        ];

        $this->dispatch('filters-updated', $this->filters);
    }

    public function exportCSV()
    {
        $data = $this->getFilteredData();

        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');

            // Headers
            fputcsv($handle, ['Ημερομηνία', 'Κατάστημα', 'Τύπος', 'Κατηγορία', 'Ποσό', 'Περιγραφή', 'Κατάσταση']);

            // Data
            foreach ($data as $transaction) {
                fputcsv($handle, [
                    $transaction->transaction_date->format('d/m/Y'),
                    $transaction->store->name,
                    $transaction->type === 'income' ? 'Έσοδο' : 'Έξοδο',
                    $transaction->category->name,
                    '€'.number_format($transaction->total, 2),
                    $transaction->description ?? '-',
                    $transaction->status === 'posted' ? 'Καταχωρημένο' : 'Εκκρεμές',
                ]);
            }

            fclose($handle);
        }, 'transactions-'.now()->format('Y-m-d').'.csv');
    }

    public function exportExcel()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\TransactionsExport($this->filters),
            'transactions-'.now()->format('Y-m-d').'.xlsx'
        );
    }

    public function exportPDF()
    {
        $data = $this->getFilteredData();
        $summary = $this->getSummary();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.transactions-pdf', [
            'transactions' => $data,
            'summary' => $summary,
            'filters' => $this->filters,
        ]);

        return $pdf->download('transactions-'.now()->format('Y-m-d').'.pdf');
    }

    public function getStores()
    {
        return auth()->user()->stores;
    }

    public function getCategories()
    {
        return Category::all();
    }

    public function getSummary(): array
    {
        $query = $this->buildQuery();

        $income = (clone $query)->where('type', 'income')->where('status', 'posted')->sum('total');
        $expense = (clone $query)->where('type', 'expense')->where('status', 'posted')->sum('total');
        $balance = $income - $expense;

        // Calculate Net Profit Margin %
        $profitMargin = $income > 0 ? ($balance / $income) * 100 : 0;

        return [
            'income' => $income,
            'expense' => $expense,
            'balance' => $balance,
            'profit_margin' => $profitMargin,
        ];
    }

    protected function buildQuery()
    {
        $query = Transaction::query();

        if ($this->filters['store_id'] !== 'all') {
            $query->where('store_id', $this->filters['store_id']);
        } else {
            // Filter by user's accessible stores
            $query->whereIn('store_id', auth()->user()->stores->pluck('id'));
        }

        if ($this->filters['date_from']) {
            $query->where('transaction_date', '>=', $this->filters['date_from']);
        }

        if ($this->filters['date_to']) {
            $query->where('transaction_date', '<=', $this->filters['date_to']);
        }

        if ($this->filters['type'] !== 'all') {
            $query->where('type', $this->filters['type']);
        }

        if ($this->filters['category_id'] !== 'all') {
            $query->where('category_id', $this->filters['category_id']);
        }

        return $query;
    }

    protected function getFilteredData()
    {
        return $this->buildQuery()->with(['category', 'store'])->get();
    }
}
