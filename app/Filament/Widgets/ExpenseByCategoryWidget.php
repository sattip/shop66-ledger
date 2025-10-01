<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class ExpenseByCategoryWidget extends ChartWidget
{
    protected static ?string $heading = 'Έξοδα ανά Κατηγορία (Τρέχον Μήνα)';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $storeId = app(\App\Support\StoreContext::class)->get();

        if (! $storeId) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        $currentMonth = Carbon::now();

        $categories = Category::where('store_id', $storeId)
            ->where('type', 'expense')
            ->where('is_active', true)
            ->get();

        $labels = [];
        $data = [];
        $colors = [
            'rgba(239, 68, 68, 0.8)',
            'rgba(249, 115, 22, 0.8)',
            'rgba(234, 179, 8, 0.8)',
            'rgba(34, 197, 94, 0.8)',
            'rgba(59, 130, 246, 0.8)',
            'rgba(168, 85, 247, 0.8)',
            'rgba(236, 72, 153, 0.8)',
            'rgba(20, 184, 166, 0.8)',
        ];

        foreach ($categories as $index => $category) {
            $total = Transaction::where('store_id', $storeId)
                ->where('category_id', $category->id)
                ->where('type', 'expense')
                ->where('status', 'posted')
                ->whereYear('transaction_date', $currentMonth->year)
                ->whereMonth('transaction_date', $currentMonth->month)
                ->sum('total');

            if ($total > 0) {
                $labels[] = $category->name;
                $data[] = round($total, 2);
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Έξοδα',
                    'data' => $data,
                    'backgroundColor' => array_slice($colors, 0, count($data)),
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'right',
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => "function(context) {
                            return context.label + ': €' + context.parsed.toLocaleString();
                        }",
                    ],
                ],
            ],
        ];
    }
}
