<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class CashFlowChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Ταμειακές Ροές';

    protected static ?int $sort = 2;

    protected $listeners = ['store-changed' => '$refresh', 'transaction-added' => '$refresh'];

    public ?string $filter = '6months';

    protected function getFilters(): ?array
    {
        return [
            '3months' => '3 Μήνες',
            '6months' => '6 Μήνες',
            '12months' => '12 Μήνες',
            'ytd' => 'Φέτος',
        ];
    }

    protected function getData(): array
    {
        $storeId = app(\App\Support\StoreContext::class)->get();

        if (! $storeId) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        // Determine number of months based on filter
        $monthsCount = match ($this->filter) {
            '3months' => 3,
            '12months' => 12,
            'ytd' => Carbon::now()->month,
            default => 6,
        };

        $months = [];
        $incomeData = [];
        $expenseData = [];
        $profitData = [];

        for ($i = $monthsCount - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = $date->locale('el')->translatedFormat('M Y');

            $income = Transaction::where('store_id', $storeId)
                ->where('type', 'income')
                ->where('status', 'posted')
                ->whereYear('transaction_date', $date->year)
                ->whereMonth('transaction_date', $date->month)
                ->sum('total');

            $expense = Transaction::where('store_id', $storeId)
                ->where('type', 'expense')
                ->where('status', 'posted')
                ->whereYear('transaction_date', $date->year)
                ->whereMonth('transaction_date', $date->month)
                ->sum('total');

            $incomeData[] = round($income, 2);
            $expenseData[] = round($expense, 2);
            $profitData[] = round($income - $expense, 2);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Έσοδα',
                    'data' => $incomeData,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.2)',
                    'borderColor' => 'rgb(34, 197, 94)',
                    'borderWidth' => 2,
                    'fill' => true,
                ],
                [
                    'label' => 'Έξοδα',
                    'data' => $expenseData,
                    'backgroundColor' => 'rgba(239, 68, 68, 0.2)',
                    'borderColor' => 'rgb(239, 68, 68)',
                    'borderWidth' => 2,
                    'fill' => true,
                ],
                [
                    'label' => 'Καθαρό Κέρδος',
                    'data' => $profitData,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 2,
                    'fill' => true,
                ],
            ],
            'labels' => $months,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => "function(value) { return '€' + value.toLocaleString(); }",
                    ],
                ],
            ],
        ];
    }
}
