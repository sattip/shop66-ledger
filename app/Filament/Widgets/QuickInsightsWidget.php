<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\Widget;

class QuickInsightsWidget extends Widget
{
    protected static string $view = 'filament.widgets.quick-insights-widget';

    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    protected $listeners = ['store-changed' => '$refresh', 'transaction-added' => '$refresh'];

    public function getInsights(): array
    {
        $storeId = app(\App\Support\StoreContext::class)->get();

        if (! $storeId) {
            return [];
        }

        $insights = [];
        $currentMonth = Carbon::now();
        $lastMonth = Carbon::now()->subMonth();

        // Get current and last month data
        $currentMonthIncome = Transaction::where('store_id', $storeId)
            ->where('type', 'income')
            ->where('status', 'posted')
            ->whereYear('transaction_date', $currentMonth->year)
            ->whereMonth('transaction_date', $currentMonth->month)
            ->sum('total');

        $lastMonthIncome = Transaction::where('store_id', $storeId)
            ->where('type', 'income')
            ->where('status', 'posted')
            ->whereYear('transaction_date', $lastMonth->year)
            ->whereMonth('transaction_date', $lastMonth->month)
            ->sum('total');

        $currentMonthExpenses = Transaction::where('store_id', $storeId)
            ->where('type', 'expense')
            ->where('status', 'posted')
            ->whereYear('transaction_date', $currentMonth->year)
            ->whereMonth('transaction_date', $currentMonth->month)
            ->sum('total');

        $lastMonthExpenses = Transaction::where('store_id', $storeId)
            ->where('type', 'expense')
            ->where('status', 'posted')
            ->whereYear('transaction_date', $lastMonth->year)
            ->whereMonth('transaction_date', $lastMonth->month)
            ->sum('total');

        // Income insights
        if ($lastMonthIncome > 0) {
            $incomeChange = (($currentMonthIncome - $lastMonthIncome) / $lastMonthIncome) * 100;
            if (abs($incomeChange) > 5) {
                $insights[] = [
                    'type' => $incomeChange > 0 ? 'success' : 'warning',
                    'icon' => $incomeChange > 0 ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down',
                    'message' => 'Τα έσοδα '.($incomeChange > 0 ? 'αυξήθηκαν' : 'μειώθηκαν').' κατά '.number_format(abs($incomeChange), 1).'% σε σχέση με τον προηγούμενο μήνα',
                ];
            }
        }

        // Expense insights
        if ($lastMonthExpenses > 0) {
            $expenseChange = (($currentMonthExpenses - $lastMonthExpenses) / $lastMonthExpenses) * 100;
            if (abs($expenseChange) > 5) {
                $insights[] = [
                    'type' => $expenseChange < 0 ? 'success' : 'warning',
                    'icon' => $expenseChange < 0 ? 'heroicon-o-arrow-trending-down' : 'heroicon-o-arrow-trending-up',
                    'message' => 'Τα έξοδα '.($expenseChange > 0 ? 'αυξήθηκαν' : 'μειώθηκαν').' κατά '.number_format(abs($expenseChange), 1).'% σε σχέση με τον προηγούμενο μήνα',
                ];
            }
        }

        // Profitability insight
        $currentProfit = $currentMonthIncome - $currentMonthExpenses;
        $profitMargin = $currentMonthIncome > 0 ? ($currentProfit / $currentMonthIncome) * 100 : 0;

        if ($profitMargin > 20) {
            $insights[] = [
                'type' => 'success',
                'icon' => 'heroicon-o-check-circle',
                'message' => 'Εξαιρετικό περιθώριο κέρδους '.number_format($profitMargin, 1).'% αυτόν τον μήνα',
            ];
        } elseif ($profitMargin < 0) {
            $insights[] = [
                'type' => 'danger',
                'icon' => 'heroicon-o-exclamation-circle',
                'message' => 'Προσοχή: Αρνητικό περιθώριο κέρδους '.number_format($profitMargin, 1).'% - τα έξοδα υπερβαίνουν τα έσοδα',
            ];
        }

        // High value transactions
        $highValueCount = Transaction::where('store_id', $storeId)
            ->where('type', 'income')
            ->where('status', 'posted')
            ->whereYear('transaction_date', $currentMonth->year)
            ->whereMonth('transaction_date', $currentMonth->month)
            ->where('total', '>', 1000)
            ->count();

        if ($highValueCount > 0) {
            $insights[] = [
                'type' => 'info',
                'icon' => 'heroicon-o-star',
                'message' => 'Πραγματοποιήθηκαν '.$highValueCount.' συναλλαγές υψηλής αξίας (>€1,000) αυτόν τον μήνα',
            ];
        }

        return $insights;
    }
}
