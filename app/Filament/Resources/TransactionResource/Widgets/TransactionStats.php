<?php

namespace App\Filament\Resources\TransactionResource\Widgets;

use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class TransactionStats extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        // Current month income
        $monthlyIncome = Transaction::where('type', 'income')
            ->where('status', '!=', 'cancelled')
            ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
            ->sum('total');

        // Current month expenses
        $monthlyExpense = Transaction::where('type', 'expense')
            ->where('status', '!=', 'cancelled')
            ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
            ->sum('total');

        // Balance
        $balance = $monthlyIncome - $monthlyExpense;

        // Today's transactions
        $todayCount = Transaction::whereDate('transaction_date', $now->toDateString())
            ->where('status', '!=', 'cancelled')
            ->count();

        // Pending transactions
        $pendingCount = Transaction::where('status', 'pending')->count();

        // Previous month comparison
        $prevMonthStart = $now->copy()->subMonth()->startOfMonth();
        $prevMonthEnd = $now->copy()->subMonth()->endOfMonth();
        $prevMonthIncome = Transaction::where('type', 'income')
            ->where('status', '!=', 'cancelled')
            ->whereBetween('transaction_date', [$prevMonthStart, $prevMonthEnd])
            ->sum('total');

        $incomeChange = $prevMonthIncome > 0
            ? round((($monthlyIncome - $prevMonthIncome) / $prevMonthIncome) * 100, 1)
            : 0;

        return [
            Stat::make('Έσοδα Μήνα', '€ '.number_format($monthlyIncome, 2, ',', '.'))
                ->description($incomeChange >= 0 ? '+'.$incomeChange.'% από προηγ. μήνα' : $incomeChange.'% από προηγ. μήνα')
                ->descriptionIcon($incomeChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($incomeChange >= 0 ? 'success' : 'danger')
                ->chart($this->getMonthlyIncomeChart()),

            Stat::make('Έξοδα Μήνα', '€ '.number_format($monthlyExpense, 2, ',', '.'))
                ->description('Τρέχων μήνας')
                ->color('warning')
                ->chart($this->getMonthlyExpenseChart()),

            Stat::make('Υπόλοιπο', '€ '.number_format($balance, 2, ',', '.'))
                ->description($balance >= 0 ? 'Θετικό υπόλοιπο' : 'Αρνητικό υπόλοιπο')
                ->descriptionIcon($balance >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($balance >= 0 ? 'success' : 'danger')
                ->extraAttributes([
                    'class' => $balance >= 0 ? '' : 'ring-danger-600 dark:ring-danger-500',
                ]),

            Stat::make('Σημερινές Συναλλαγές', $todayCount)
                ->description('Συναλλαγές σήμερα')
                ->color('info')
                ->icon('heroicon-o-calendar'),

            Stat::make('Σε Αναμονή', $pendingCount)
                ->description('Περιμένουν έγκριση')
                ->color('warning')
                ->icon('heroicon-o-clock')
                ->url(route('filament.admin.resources.transactions.index', ['tableFilters[status][value]' => 'pending'])),
        ];
    }

    protected function getMonthlyIncomeChart(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $data[] = Transaction::where('type', 'income')
                ->where('status', '!=', 'cancelled')
                ->whereDate('transaction_date', $date)
                ->sum('total');
        }

        return $data;
    }

    protected function getMonthlyExpenseChart(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $data[] = Transaction::where('type', 'expense')
                ->where('status', '!=', 'cancelled')
                ->whereDate('transaction_date', $date)
                ->sum('total');
        }

        return $data;
    }
}
