<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class TopVendorsChart extends ChartWidget
{
    protected static ?string $heading = 'Top 10 Προμηθευτές';

    protected static ?string $description = 'Κατάταξη προμηθευτών βάσει συνολικών αγορών';

    protected static ?int $sort = 2;

    public ?string $filter = 'year';

    protected function getFilters(): ?array
    {
        return [
            'month' => 'Τρέχων Μήνας',
            'quarter' => 'Τρέχον Τρίμηνο',
            'year' => 'Τρέχον Έτος',
            'all' => 'Όλα',
        ];
    }

    protected function getData(): array
    {
        $query = Invoice::query()
            ->selectRaw('vendors.name as vendor_name, SUM(invoices.total_amount) as total')
            ->join('vendors', 'invoices.vendor_id', '=', 'vendors.id')
            ->where('invoices.status', 'paid')
            ->groupBy('vendors.id', 'vendors.name')
            ->orderByDesc('total')
            ->limit(10);

        switch ($this->filter) {
            case 'month':
                $query->whereMonth('invoice_date', Carbon::now()->month)
                    ->whereYear('invoice_date', Carbon::now()->year);
                break;
            case 'quarter':
                $query->whereQuarter('invoice_date', Carbon::now()->quarter)
                    ->whereYear('invoice_date', Carbon::now()->year);
                break;
            case 'year':
                $query->whereYear('invoice_date', Carbon::now()->year);
                break;
        }

        $vendors = $query->get();

        return [
            'datasets' => [
                [
                    'label' => 'Συνολικές Αγορές (€)',
                    'data' => $vendors->pluck('total')->toArray(),
                    'backgroundColor' => [
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(153, 102, 255, 0.7)',
                        'rgba(255, 159, 64, 0.7)',
                        'rgba(199, 199, 199, 0.7)',
                        'rgba(83, 102, 255, 0.7)',
                        'rgba(255, 99, 255, 0.7)',
                        'rgba(99, 255, 132, 0.7)',
                    ],
                ],
            ],
            'labels' => $vendors->pluck('vendor_name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
        ];
    }
}
