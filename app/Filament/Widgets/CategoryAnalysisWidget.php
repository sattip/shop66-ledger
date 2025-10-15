<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\Widget;

class CategoryAnalysisWidget extends Widget
{
    protected static string $view = 'filament.widgets.category-analysis-widget';

    protected static ?int $sort = 3;

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

    public function getCategoryBreakdown(): array
    {
        if (!auth()->check() || !auth()->user()->stores) {
            return ['labels' => [], 'data' => [], 'colors' => []];
        }

        $query = $this->buildQuery();

        $categories = Category::query()
            ->when($this->filters['store_id'] !== 'all', function ($q) {
                $q->where('store_id', $this->filters['store_id']);
            })
            ->when($this->filters['store_id'] === 'all', function ($q) {
                $q->whereIn('store_id', auth()->user()->stores->pluck('id'));
            })
            ->where('is_active', true)
            ->get();

        $labels = [];
        $data = [];
        $colors = [
            'rgba(239, 68, 68, 0.8)', 'rgba(249, 115, 22, 0.8)', 'rgba(234, 179, 8, 0.8)',
            'rgba(34, 197, 94, 0.8)', 'rgba(59, 130, 246, 0.8)', 'rgba(168, 85, 247, 0.8)',
            'rgba(236, 72, 153, 0.8)', 'rgba(20, 184, 166, 0.8)', 'rgba(245, 158, 11, 0.8)',
            'rgba(99, 102, 241, 0.8)',
        ];

        foreach ($categories as $category) {
            $total = (clone $query)
                ->where('category_id', $category->id)
                ->where('status', 'posted')
                ->sum('total');

            if ($total > 0) {
                $labels[] = $category->name;
                $data[] = round($total, 2);
            }
        }

        return [
            'labels' => $labels,
            'data' => $data,
            'colors' => array_slice($colors, 0, count($data)),
        ];
    }

    public function getTopCategories(): array
    {
        $query = $this->buildQuery()->where('status', 'posted');

        // Top 5 by Revenue
        $topRevenue = (clone $query)
            ->where('type', 'income')
            ->selectRaw('category_id, SUM(total) as total')
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                $category = Category::find($item->category_id);

                return [
                    'name' => $category?->name ?? 'Unknown',
                    'total' => $item->total,
                ];
            });

        // Top 5 by Expenses
        $topExpenses = (clone $query)
            ->where('type', 'expense')
            ->selectRaw('category_id, SUM(total) as total')
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                $category = Category::find($item->category_id);

                return [
                    'name' => $category?->name ?? 'Unknown',
                    'total' => $item->total,
                ];
            });

        return [
            'revenue' => $topRevenue->toArray(),
            'expenses' => $topExpenses->toArray(),
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

        if ($this->filters['type'] !== 'all') {
            $query->where('type', $this->filters['type']);
        }

        return $query;
    }
}
