<?php

namespace App\Filament\Widgets;

use App\Models\InvoiceItem;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class CategoryBreakdown extends ChartWidget
{
    protected static ?string $heading = 'Κατανομή ανά Κατηγορία';

    protected static ?string $description = 'Ανάλυση δαπανών ανά κατηγορία προϊόντων';

    protected static ?int $sort = 3;

    public ?string $filter = 'month';

    protected function getFilters(): ?array
    {
        return [
            'week' => 'Τρέχουσα Εβδομάδα',
            'month' => 'Τρέχων Μήνας',
            'year' => 'Τρέχον Έτος',
        ];
    }

    protected function getData(): array
    {
        $query = InvoiceItem::query()
            ->selectRaw('categories.name as category_name, SUM(invoice_items.total) as total')
            ->join('items', 'invoice_items.item_id', '=', 'items.id')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->where('invoices.status', 'paid')
            ->groupBy('categories.id', 'categories.name');

        switch ($this->filter) {
            case 'week':
                $query->whereBetween('invoices.invoice_date', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek(),
                ]);
                break;
            case 'month':
                $query->whereMonth('invoices.invoice_date', Carbon::now()->month)
                    ->whereYear('invoices.invoice_date', Carbon::now()->year);
                break;
            case 'year':
                $query->whereYear('invoices.invoice_date', Carbon::now()->year);
                break;
        }

        $categories = $query->get();

        // If no categories, show a default dataset
        if ($categories->isEmpty()) {
            return [
                'datasets' => [
                    [
                        'data' => [0],
                        'backgroundColor' => ['rgba(200, 200, 200, 0.5)'],
                    ],
                ],
                'labels' => ['Χωρίς Δεδομένα'],
            ];
        }

        return [
            'datasets' => [
                [
                    'data' => $categories->pluck('total')->toArray(),
                    'backgroundColor' => [
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(153, 102, 255, 0.8)',
                        'rgba(255, 159, 64, 0.8)',
                        'rgba(199, 199, 199, 0.8)',
                        'rgba(83, 102, 255, 0.8)',
                    ],
                ],
            ],
            'labels' => $categories->pluck('category_name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
