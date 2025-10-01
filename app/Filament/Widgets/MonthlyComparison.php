<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class MonthlyComparison extends ChartWidget
{
    protected static ?string $heading = 'Σύγκριση Μηνιαίων Εσόδων/Εξόδων';

    protected static ?string $description = 'Ανάλυση εσόδων και εξόδων ανά μήνα';

    protected static ?int $sort = 4;

    protected function getData(): array
    {
        $months = [];
        $revenues = [];
        $expenses = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);

            // Calculate revenue (paid invoices)
            $revenue = Invoice::where('status', 'paid')
                ->whereYear('invoice_date', $month->year)
                ->whereMonth('invoice_date', $month->month)
                ->sum('total_amount');

            // Calculate expenses (outgoing transactions)
            $expense = Transaction::where('type', 'expense')
                ->whereYear('transaction_date', $month->year)
                ->whereMonth('transaction_date', $month->month)
                ->sum('amount');

            $months[] = $month->locale('el')->isoFormat('MMM YYYY');
            $revenues[] = $revenue;
            $expenses[] = abs($expense); // Make positive for display
        }

        return [
            'datasets' => [
                [
                    'label' => 'Έσοδα',
                    'data' => $revenues,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.7)',
                    'borderColor' => 'rgb(34, 197, 94)',
                ],
                [
                    'label' => 'Έξοδα',
                    'data' => $expenses,
                    'backgroundColor' => 'rgba(239, 68, 68, 0.7)',
                    'borderColor' => 'rgb(239, 68, 68)',
                ],
            ],
            'labels' => $months,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => "function(value) { return '€' + value.toLocaleString(); }",
                    ],
                ],
            ],
            'plugins' => [
                'tooltip' => [
                    'callbacks' => [
                        'label' => "function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': €';
                            }
                            if (context.parsed.y !== null) {
                                label += context.parsed.y.toLocaleString();
                            }
                            return label;
                        }",
                    ],
                ],
            ],
        ];
    }
}
