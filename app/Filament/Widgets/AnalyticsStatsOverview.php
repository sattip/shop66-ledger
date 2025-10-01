<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class AnalyticsStatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $today = Carbon::today();
        $thisYear = Carbon::now()->year;

        // Yearly revenue
        $yearlyRevenue = Invoice::where('status', 'paid')
            ->whereYear('invoice_date', $thisYear)
            ->sum('total_amount');

        // Average invoice value
        $avgInvoiceValue = Invoice::where('status', 'paid')
            ->whereYear('invoice_date', $thisYear)
            ->avg('total_amount') ?? 0;

        // Total outstanding
        $totalOutstanding = Invoice::whereIn('status', ['pending', 'overdue'])
            ->sum('total_amount');

        // Growth rate (comparing to last year)
        $lastYearRevenue = Invoice::where('status', 'paid')
            ->whereYear('invoice_date', $thisYear - 1)
            ->sum('total_amount');

        $growthRate = $lastYearRevenue > 0
            ? (($yearlyRevenue - $lastYearRevenue) / $lastYearRevenue) * 100
            : 100;

        return [
            Stat::make('Ετήσια Έσοδα '.$thisYear, '€'.number_format($yearlyRevenue, 2))
                ->description('Συνολικά έσοδα έτους')
                ->descriptionIcon('heroicon-m-currency-euro')
                ->color('success'),

            Stat::make('Μέση Αξία Τιμολογίου', '€'.number_format($avgInvoiceValue, 2))
                ->description('Μέσος όρος τιμολογίων')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('primary'),

            Stat::make('Εκκρεμή Ποσά', '€'.number_format($totalOutstanding, 2))
                ->description('Αναμένουν είσπραξη')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning'),

            Stat::make('Ετήσια Ανάπτυξη', ($growthRate >= 0 ? '+' : '').number_format($growthRate, 1).'%')
                ->description('Σε σχέση με πέρυσι')
                ->descriptionIcon($growthRate >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($growthRate >= 0 ? 'success' : 'danger')
                ->chart($growthRate >= 0 ? [4, 5, 3, 7, 3, 5, 8, 10] : [10, 8, 7, 6, 5, 4, 3, 2]),
        ];
    }
}
