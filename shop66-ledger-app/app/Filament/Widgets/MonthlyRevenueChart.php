<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class MonthlyRevenueChart extends ChartWidget
{
    protected static ?string $heading = 'Μηνιαία Έσοδα';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $data = [];
        $labels = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $revenue = Invoice::where('status', 'paid')
                ->whereYear('invoice_date', $month->year)
                ->whereMonth('invoice_date', $month->month)
                ->sum('total_amount');

            $data[] = $revenue;
            $labels[] = $month->locale('el')->isoFormat('MMM');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Έσοδα (€)',
                    'data' => $data,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgb(59, 130, 246)',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
