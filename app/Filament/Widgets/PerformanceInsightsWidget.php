<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\Widget;

class PerformanceInsightsWidget extends Widget
{
    protected static string $view = 'filament.widgets.performance-insights-widget';

    protected static ?int $sort = 2;

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

    public function getPerformanceMetrics(): array
    {
        // Current period totals
        $currentIncome = $this->buildQuery()
            ->where('type', 'income')
            ->where('status', 'posted')
            ->sum('total');

        $currentExpense = $this->buildQuery()
            ->where('type', 'expense')
            ->where('status', 'posted')
            ->sum('total');

        // Calculate previous period (same duration, before current period)
        $dateFrom = Carbon::parse($this->filters['date_from']);
        $dateTo = Carbon::parse($this->filters['date_to']);
        $duration = $dateFrom->diffInDays($dateTo);

        $prevDateFrom = $dateFrom->copy()->subDays($duration + 1);
        $prevDateTo = $dateFrom->copy()->subDay();

        $prevIncome = $this->buildPreviousPeriodQuery($prevDateFrom, $prevDateTo)
            ->where('type', 'income')
            ->where('status', 'posted')
            ->sum('total');

        $prevExpense = $this->buildPreviousPeriodQuery($prevDateFrom, $prevDateTo)
            ->where('type', 'expense')
            ->where('status', 'posted')
            ->sum('total');

        // Calculate growth percentages
        $revenueGrowth = $prevIncome > 0
            ? (($currentIncome - $prevIncome) / $prevIncome) * 100
            : 0;

        $expenseGrowth = $prevExpense > 0
            ? (($currentExpense - $prevExpense) / $prevExpense) * 100
            : 0;

        return [
            'revenue_growth' => $revenueGrowth,
            'expense_growth' => $expenseGrowth,
            'current_income' => $currentIncome,
            'current_expense' => $currentExpense,
            'prev_income' => $prevIncome,
            'prev_expense' => $prevExpense,
        ];
    }

    public function getTrendData(): array
    {
        $dateFrom = Carbon::parse($this->filters['date_from']);
        $dateTo = Carbon::parse($this->filters['date_to']);
        $duration = $dateFrom->diffInDays($dateTo);

        // Determine if we should group by day, week, or month
        $groupBy = match (true) {
            $duration <= 31 => 'day',
            $duration <= 90 => 'week',
            default => 'month',
        };

        $labels = [];
        $incomeData = [];
        $expenseData = [];
        $netResultData = [];

        $currentDate = $dateFrom->copy();

        while ($currentDate <= $dateTo) {
            $periodStart = $currentDate->copy();
            $periodEnd = match ($groupBy) {
                'day' => $currentDate->copy(),
                'week' => $currentDate->copy()->endOfWeek()->min($dateTo),
                'month' => $currentDate->copy()->endOfMonth()->min($dateTo),
            };

            $labels[] = match ($groupBy) {
                'day' => $periodStart->locale('el')->translatedFormat('d M'),
                'week' => $periodStart->locale('el')->translatedFormat('d M').' - '.$periodEnd->locale('el')->translatedFormat('d M'),
                'month' => $periodStart->locale('el')->translatedFormat('M Y'),
            };

            $income = $this->buildTrendQuery($periodStart, $periodEnd)
                ->where('type', 'income')
                ->where('status', 'posted')
                ->sum('total');

            $expense = $this->buildTrendQuery($periodStart, $periodEnd)
                ->where('type', 'expense')
                ->where('status', 'posted')
                ->sum('total');

            $incomeData[] = round($income, 2);
            $expenseData[] = round($expense, 2);
            $netResultData[] = round($income - $expense, 2);

            $currentDate = match ($groupBy) {
                'day' => $currentDate->addDay(),
                'week' => $currentDate->addWeek(),
                'month' => $currentDate->addMonth(),
            };
        }

        return [
            'labels' => $labels,
            'income' => $incomeData,
            'expense' => $expenseData,
            'net_result' => $netResultData,
        ];
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

    protected function buildPreviousPeriodQuery($from, $to)
    {
        $query = Transaction::query();

        if ($this->filters['store_id'] !== 'all') {
            $query->where('store_id', $this->filters['store_id']);
        } else {
            $query->whereIn('store_id', auth()->user()->stores->pluck('id'));
        }

        $query->whereBetween('transaction_date', [$from->format('Y-m-d'), $to->format('Y-m-d')]);

        if ($this->filters['category_id'] !== 'all') {
            $query->where('category_id', $this->filters['category_id']);
        }

        return $query;
    }

    protected function buildTrendQuery($from, $to)
    {
        $query = Transaction::query();

        if ($this->filters['store_id'] !== 'all') {
            $query->where('store_id', $this->filters['store_id']);
        } else {
            $query->whereIn('store_id', auth()->user()->stores->pluck('id'));
        }

        $query->whereBetween('transaction_date', [$from->format('Y-m-d'), $to->format('Y-m-d')]);

        if ($this->filters['category_id'] !== 'all') {
            $query->where('category_id', $this->filters['category_id']);
        }

        return $query;
    }
}
