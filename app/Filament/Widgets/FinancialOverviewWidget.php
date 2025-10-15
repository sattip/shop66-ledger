<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FinancialOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected $listeners = ['store-changed' => '$refresh', 'transaction-added' => '$refresh'];

    protected function getStats(): array
    {
        $storeId = app(\App\Support\StoreContext::class)->get();

        if (! $storeId) {
            return [];
        }

        // Get current month data
        $currentMonthStart = Carbon::now()->startOfMonth();
        $currentMonthEnd = Carbon::now()->endOfMonth();
        $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        // Total Balance (Income - Expenses)
        $totalIncome = Transaction::where('store_id', $storeId)
            ->where('type', 'income')
            ->where('status', 'posted')
            ->sum('total');

        $totalExpenses = Transaction::where('store_id', $storeId)
            ->where('type', 'expense')
            ->where('status', 'posted')
            ->sum('total');

        $balance = $totalIncome - $totalExpenses;

        // Current Month Income
        $currentMonthIncome = Transaction::where('store_id', $storeId)
            ->where('type', 'income')
            ->where('status', 'posted')
            ->whereBetween('transaction_date', [$currentMonthStart, $currentMonthEnd])
            ->sum('total');

        $lastMonthIncome = Transaction::where('store_id', $storeId)
            ->where('type', 'income')
            ->where('status', 'posted')
            ->whereBetween('transaction_date', [$lastMonthStart, $lastMonthEnd])
            ->sum('total');

        $incomeChange = $lastMonthIncome > 0
            ? (($currentMonthIncome - $lastMonthIncome) / $lastMonthIncome) * 100
            : 0;

        // Current Month Expenses
        $currentMonthExpenses = Transaction::where('store_id', $storeId)
            ->where('type', 'expense')
            ->where('status', 'posted')
            ->whereBetween('transaction_date', [$currentMonthStart, $currentMonthEnd])
            ->sum('total');

        $lastMonthExpenses = Transaction::where('store_id', $storeId)
            ->where('type', 'expense')
            ->where('status', 'posted')
            ->whereBetween('transaction_date', [$lastMonthStart, $lastMonthEnd])
            ->sum('total');

        $expenseChange = $lastMonthExpenses > 0
            ? (($currentMonthExpenses - $lastMonthExpenses) / $lastMonthExpenses) * 100
            : 0;

        // Profit/Loss this month
        $currentMonthProfit = $currentMonthIncome - $currentMonthExpenses;
        $lastMonthProfit = $lastMonthIncome - $lastMonthExpenses;

        $profitChange = $lastMonthProfit != 0
            ? (($currentMonthProfit - $lastMonthProfit) / abs($lastMonthProfit)) * 100
            : 0;

        // Calculate additional KPIs
        $daysInMonth = Carbon::now()->daysInMonth;
        $daysPassed = Carbon::now()->day;
        $avgDailyIncome = $daysPassed > 0 ? $currentMonthIncome / $daysPassed : 0;
        $avgDailyExpense = $daysPassed > 0 ? $currentMonthExpenses / $daysPassed : 0;

        // Cash Runway (months of expenses covered by balance)
        $monthlyAvgExpense = $lastMonthExpenses > 0 ? $lastMonthExpenses : $currentMonthExpenses;
        $cashRunway = $monthlyAvgExpense > 0 ? $balance / $monthlyAvgExpense : 0;

        // Average Transaction Value
        $transactionCount = Transaction::where('store_id', $storeId)
            ->where('type', 'income')
            ->where('status', 'posted')
            ->whereBetween('transaction_date', [$currentMonthStart, $currentMonthEnd])
            ->count();
        $avgTransactionValue = $transactionCount > 0 ? $currentMonthIncome / $transactionCount : 0;

        return [
            Stat::make('Συνολικό Υπόλοιπο', '€'.number_format($balance, 2))
                ->description('Σύνολο Εσόδων - Σύνολο Εξόδων')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color($balance >= 0 ? 'success' : 'danger')
                ->chart($this->getBalanceTrend($storeId)),

            Stat::make('Έσοδα Μήνα', '€'.number_format($currentMonthIncome, 2))
                ->description(($incomeChange >= 0 ? '+' : '').number_format($incomeChange, 1).'% από τον προηγούμενο μήνα')
                ->descriptionIcon($incomeChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($incomeChange >= 0 ? 'success' : 'warning')
                ->chart($this->getIncomeTrend($storeId)),

            Stat::make('Έξοδα Μήνα', '€'.number_format($currentMonthExpenses, 2))
                ->description(($expenseChange >= 0 ? '+' : '').number_format($expenseChange, 1).'% από τον προηγούμενο μήνα')
                ->descriptionIcon($expenseChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($expenseChange >= 0 ? 'danger' : 'success')
                ->chart($this->getExpenseTrend($storeId)),

            Stat::make('Καθαρό Κέρδος Μήνα', '€'.number_format($currentMonthProfit, 2))
                ->description(($profitChange >= 0 ? '+' : '').number_format($profitChange, 1).'% από τον προηγούμενο μήνα')
                ->descriptionIcon($profitChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($currentMonthProfit >= 0 ? 'success' : 'danger'),

            Stat::make('Μέσα Ημερήσια Έσοδα', '€'.number_format($avgDailyIncome, 2))
                ->description('Βάσει '.$daysPassed.' ημερών του μήνα')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info')
                ->chart($this->getIncomeTrend($storeId)),

            Stat::make('Μέσα Ημερήσια Έξοδα', '€'.number_format($avgDailyExpense, 2))
                ->description('Ρυθμός καύσης κεφαλαίων')
                ->descriptionIcon('heroicon-m-fire')
                ->color('warning')
                ->chart($this->getExpenseTrend($storeId)),

            Stat::make('Διάρκεια Κεφαλαίων', number_format($cashRunway, 1).' μήνες')
                ->description($balance >= 0 ? 'Κάλυψη με τρέχον υπόλοιπο' : 'Αρνητικό υπόλοιπο')
                ->descriptionIcon('heroicon-m-clock')
                ->color($cashRunway >= 3 ? 'success' : ($cashRunway >= 1 ? 'warning' : 'danger')),

            Stat::make('Μέση Αξία Συναλλαγής', '€'.number_format($avgTransactionValue, 2))
                ->description($transactionCount.' συναλλαγές αυτόν τον μήνα')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('info'),
        ];
    }

    protected function getBalanceTrend(int $storeId): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $income = Transaction::where('store_id', $storeId)
                ->where('type', 'income')
                ->where('status', 'posted')
                ->whereYear('transaction_date', $date->year)
                ->whereMonth('transaction_date', $date->month)
                ->sum('total');

            $expense = Transaction::where('store_id', $storeId)
                ->where('type', 'expense')
                ->where('status', 'posted')
                ->whereYear('transaction_date', $date->year)
                ->whereMonth('transaction_date', $date->month)
                ->sum('total');

            $data[] = $income - $expense;
        }

        return $data;
    }

    protected function getIncomeTrend(int $storeId): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $income = Transaction::where('store_id', $storeId)
                ->where('type', 'income')
                ->where('status', 'posted')
                ->whereYear('transaction_date', $date->year)
                ->whereMonth('transaction_date', $date->month)
                ->sum('total');

            $data[] = $income;
        }

        return $data;
    }

    protected function getExpenseTrend(int $storeId): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $expense = Transaction::where('store_id', $storeId)
                ->where('type', 'expense')
                ->where('status', 'posted')
                ->whereYear('transaction_date', $date->year)
                ->whereMonth('transaction_date', $date->month)
                ->sum('total');

            $data[] = $expense;
        }

        return $data;
    }
}
