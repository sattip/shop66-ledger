<?php

namespace App\Filament\Widgets;

use App\Models\Store;
use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\Widget;

class StoreAnalysisWidget extends Widget
{
    protected static string $view = 'filament.widgets.store-analysis-widget';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    protected $listeners = ['store-changed' => '$refresh', 'filters-updated' => 'handleFiltersUpdated'];

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

    public function handleFiltersUpdated($filters): void
    {
        $this->filters = $filters;
    }

    public function getTopStoresByRevenue(): array
    {
        if (!auth()->check() || !auth()->user()->stores) {
            return [];
        }

        // Build query ignoring store filter for this chart
        $query = Transaction::query()
            ->whereIn('store_id', auth()->user()->stores->pluck('id'));

        if ($this->filters['date_from']) {
            $query->where('transaction_date', '>=', $this->filters['date_from']);
        }

        if ($this->filters['date_to']) {
            $query->where('transaction_date', '<=', $this->filters['date_to']);
        }

        if ($this->filters['category_id'] !== 'all') {
            $query->where('category_id', $this->filters['category_id']);
        }

        return $query
            ->where('type', 'income')
            ->where('status', 'posted')
            ->selectRaw('store_id, SUM(total) as total')
            ->groupBy('store_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                $store = Store::find($item->store_id);

                return [
                    'name' => $store?->name ?? 'Unknown',
                    'total' => $item->total,
                ];
            })
            ->toArray();
    }

    public function getTopStoresByProfitability(): array
    {
        if (!auth()->check() || !auth()->user()->stores) {
            return [];
        }

        $stores = auth()->user()->stores;
        $profitability = [];

        foreach ($stores as $store) {
            $income = $this->buildQuery()
                ->where('store_id', $store->id)
                ->where('type', 'income')
                ->where('status', 'posted')
                ->sum('total');

            $expense = $this->buildQuery()
                ->where('store_id', $store->id)
                ->where('type', 'expense')
                ->where('status', 'posted')
                ->sum('total');

            $profit = $income - $expense;
            $profitMargin = $income > 0 ? ($profit / $income) * 100 : 0;

            $profitability[] = [
                'name' => $store->name,
                'profit' => $profit,
                'profit_margin' => $profitMargin,
                'income' => $income,
                'expense' => $expense,
            ];
        }

        // Sort by profit margin descending
        usort($profitability, function ($a, $b) {
            return $b['profit_margin'] <=> $a['profit_margin'];
        });

        return array_slice($profitability, 0, 5);
    }

    public function getStoreChartData(): array
    {
        $topStores = $this->getTopStoresByRevenue();

        return [
            'labels' => array_column($topStores, 'name'),
            'data' => array_column($topStores, 'total'),
        ];
    }

    public function getIncomeExpenseChartData(): array
    {
        if (!auth()->check() || !auth()->user()->stores) {
            return [
                'labels' => [],
                'income' => [],
                'expense' => [],
            ];
        }

        $stores = auth()->user()->stores->take(5);
        $labels = [];
        $incomeData = [];
        $expenseData = [];

        foreach ($stores as $store) {
            $labels[] = $store->name;

            // Build a fresh query for each store, ignoring store filter for this chart
            $baseQuery = Transaction::query()
                ->where('store_id', $store->id);

            if ($this->filters['date_from']) {
                $baseQuery->where('transaction_date', '>=', $this->filters['date_from']);
            }

            if ($this->filters['date_to']) {
                $baseQuery->where('transaction_date', '<=', $this->filters['date_to']);
            }

            if ($this->filters['category_id'] !== 'all') {
                $baseQuery->where('category_id', $this->filters['category_id']);
            }

            $income = (clone $baseQuery)
                ->where('type', 'income')
                ->where('status', 'posted')
                ->sum('total');

            $expense = (clone $baseQuery)
                ->where('type', 'expense')
                ->where('status', 'posted')
                ->sum('total');

            $incomeData[] = $income;
            $expenseData[] = $expense;
        }

        return [
            'labels' => $labels,
            'income' => $incomeData,
            'expense' => $expenseData,
        ];
    }

    protected function buildQuery()
    {
        $query = Transaction::query();

        if ($this->filters['store_id'] !== 'all') {
            $query->where('store_id', $this->filters['store_id']);
        } else {
            if (auth()->check() && auth()->user()->stores) {
                $query->whereIn('store_id', auth()->user()->stores->pluck('id'));
            }
        }

        if ($this->filters['date_from']) {
            $query->where('transaction_date', '>=', $this->filters['date_from']);
        }

        if ($this->filters['date_to']) {
            $query->where('transaction_date', '<=', $this->filters['date_to']);
        }

        if ($this->filters['category_id'] !== 'all') {
            $query->where('category_id', $this->filters['category_id']);
        }

        return $query;
    }
}
