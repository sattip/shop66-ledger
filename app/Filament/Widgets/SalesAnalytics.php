<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class SalesAnalytics extends ChartWidget
{
    protected static ?string $heading = 'Ανάλυση Πωλήσεων';

    protected static ?string $description = 'Εβδομαδιαία ανάλυση εσόδων';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 1;

    public ?string $filter = '7';

    protected function getFilters(): ?array
    {
        return [
            '7' => 'Τελευταίες 7 ημέρες',
            '30' => 'Τελευταίες 30 ημέρες',
            '90' => 'Τελευταίες 90 ημέρες',
            '365' => 'Τελευταίος χρόνος',
        ];
    }

    protected function getData(): array
    {
        $days = (int) $this->filter;
        $data = [];
        $labels = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $revenue = Invoice::where('status', 'paid')
                ->whereDate('invoice_date', $date)
                ->sum('total_amount');

            $data[] = $revenue;

            if ($days <= 30) {
                $labels[] = $date->format('d/m');
            } elseif ($days <= 90) {
                $labels[] = $date->format('d/m');
            } else {
                $labels[] = $date->format('M');
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Έσοδα (€)',
                    'data' => $data,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'tension' => 0.3,
                    'fill' => true,
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
