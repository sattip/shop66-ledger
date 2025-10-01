<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Vendor;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StoreStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $thisMonth = now()->startOfMonth();
        $lastMonth = now()->subMonth()->startOfMonth();

        $currentMonthRevenue = Invoice::where('status', 'paid')
            ->where('invoice_date', '>=', $thisMonth)
            ->sum('total_amount');

        $lastMonthRevenue = Invoice::where('status', 'paid')
            ->whereBetween('invoice_date', [$lastMonth, $thisMonth])
            ->sum('total_amount');

        $revenueChange = $lastMonthRevenue > 0
            ? (($currentMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100
            : 0;

        $pendingInvoices = Invoice::where('status', 'pending')->count();
        $totalVendors = Vendor::count();
        $totalCustomers = Customer::count();

        return [
            Stat::make('Έσοδα Μήνα', '€'.number_format($currentMonthRevenue, 2))
                ->description($revenueChange >= 0 ? '+'.number_format($revenueChange, 1).'% από προηγ. μήνα' : number_format($revenueChange, 1).'% από προηγ. μήνα')
                ->descriptionIcon($revenueChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($revenueChange >= 0 ? 'success' : 'danger')
                ->chart([7, 3, 4, 5, 6, 3, 5, 8]),

            Stat::make('Εκκρεμή Τιμολόγια', $pendingInvoices)
                ->description('Αναμένουν πληρωμή')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Συνολικοί Προμηθευτές', $totalVendors)
                ->description('Ενεργοί προμηθευτές')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('primary'),
        ];
    }
}
