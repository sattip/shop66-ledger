<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\Widget;

class EfficiencyRatiosWidget extends Widget
{
    protected static string $view = 'filament.widgets.efficiency-ratios-widget';

    protected static ?int $sort = 5;

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

    public function getEfficiencyMetrics(): array
    {
        $query = $this->buildQuery();

        $income = (clone $query)->where('type', 'income')->where('status', 'posted')->sum('total');
        $expense = (clone $query)->where('type', 'expense')->where('status', 'posted')->sum('total');

        // Expenses as % of Revenue
        $expenseRatio = $income > 0 ? ($expense / $income) * 100 : 0;

        // Revenue per Store
        $storeCount = $this->filters['store_id'] !== 'all' ? 1 : auth()->user()->stores->count();
        $revenuePerStore = $storeCount > 0 ? $income / $storeCount : 0;

        // Break-Even Point (when cumulative revenue >= cumulative expenses)
        $breakEvenDate = $this->calculateBreakEvenPoint();

        // Days in period
        $dateFrom = Carbon::parse($this->filters['date_from']);
        $dateTo = Carbon::parse($this->filters['date_to']);
        $daysInPeriod = $dateFrom->diffInDays($dateTo) + 1;

        // Average daily revenue and expense
        $avgDailyRevenue = $daysInPeriod > 0 ? $income / $daysInPeriod : 0;
        $avgDailyExpense = $daysInPeriod > 0 ? $expense / $daysInPeriod : 0;

        return [
            'expense_ratio' => $expenseRatio,
            'revenue_per_store' => $revenuePerStore,
            'break_even_date' => $breakEvenDate,
            'avg_daily_revenue' => $avgDailyRevenue,
            'avg_daily_expense' => $avgDailyExpense,
            'store_count' => $storeCount,
        ];
    }

    protected function calculateBreakEvenPoint(): ?string
    {
        $dateFrom = Carbon::parse($this->filters['date_from']);
        $dateTo = Carbon::parse($this->filters['date_to']);

        $currentDate = $dateFrom->copy();
        $cumulativeIncome = 0;
        $cumulativeExpense = 0;

        while ($currentDate <= $dateTo) {
            $dailyIncome = $this->buildQuery()
                ->where('type', 'income')
                ->where('status', 'posted')
                ->whereDate('transaction_date', $currentDate)
                ->sum('total');

            $dailyExpense = $this->buildQuery()
                ->where('type', 'expense')
                ->where('status', 'posted')
                ->whereDate('transaction_date', $currentDate)
                ->sum('total');

            $cumulativeIncome += $dailyIncome;
            $cumulativeExpense += $dailyExpense;

            // Check if we've reached break-even
            if ($cumulativeIncome >= $cumulativeExpense && $cumulativeExpense > 0) {
                return $currentDate->locale('el')->translatedFormat('d M Y');
            }

            $currentDate->addDay();
        }

        return null; // Break-even not reached in period
    }

    protected function buildQuery()
    {
        $query = Transaction::query();

        if ($this->filters['store_id'] !== 'all') {
            $query->where('store_id', $this->filters['store_id']);
        } else {
            $query->whereIn('store_id', auth()->user()->stores->pluck('id'));
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
