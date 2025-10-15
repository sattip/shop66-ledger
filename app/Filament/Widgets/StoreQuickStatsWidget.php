<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StoreQuickStatsWidget extends BaseWidget
{
    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    protected $listeners = ['store-changed' => '$refresh', 'transaction-added' => '$refresh'];

    protected function getStats(): array
    {
        $storeId = app(\App\Support\StoreContext::class)->get();

        if (! $storeId) {
            return [];
        }

        // Get all posted transactions for this store
        $totalIncome = Transaction::where('store_id', $storeId)
            ->where('type', 'income')
            ->where('status', 'posted')
            ->sum('total');

        $totalExpenses = Transaction::where('store_id', $storeId)
            ->where('type', 'expense')
            ->where('status', 'posted')
            ->sum('total');

        $balance = $totalIncome - $totalExpenses;

        return [
            Stat::make('Έσοδα', '€'.number_format($totalIncome, 2))
                ->description('Συνολικά έσοδα')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Έξοδα', '€'.number_format($totalExpenses, 2))
                ->description('Συνολικά έξοδα')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),

            Stat::make('Καθαρό', '€'.number_format($balance, 2))
                ->description('Υπόλοιπο')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color($balance >= 0 ? 'success' : 'danger'),
        ];
    }

    protected function getColumns(): int
    {
        return 3;
    }
}
