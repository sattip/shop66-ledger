<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Transaction;
use App\Models\Vendor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $storeId = $request->route('store');
        
        // Get current and previous month date ranges
        $currentMonth = now()->startOfMonth();
        $previousMonth = now()->subMonth()->startOfMonth();
        $currentMonthEnd = now()->endOfMonth();
        $previousMonthEnd = now()->subMonth()->endOfMonth();
        
        // Get basic metrics
        $metrics = $this->getMetrics($storeId, $currentMonth, $currentMonthEnd, $previousMonth, $previousMonthEnd);
        
        // Get chart data
        $chartData = $this->getChartData($storeId);
        
        // Get recent activity
        $recentActivity = $this->getRecentActivity($storeId);
        
        return response()->json([
            'metrics' => $metrics,
            'charts' => $chartData,
            'recent_activity' => $recentActivity,
        ]);
    }
    
    private function getMetrics($storeId, $currentMonth, $currentMonthEnd, $previousMonth, $previousMonthEnd): array
    {
        // Total transactions this month
        $currentTransactions = Transaction::where('store_id', $storeId)
            ->whereBetween('transaction_date', [$currentMonth, $currentMonthEnd])
            ->count();
            
        $previousTransactions = Transaction::where('store_id', $storeId)
            ->whereBetween('transaction_date', [$previousMonth, $previousMonthEnd])
            ->count();
        
        // Total amount this month
        $currentAmount = Transaction::where('store_id', $storeId)
            ->whereBetween('transaction_date', [$currentMonth, $currentMonthEnd])
            ->sum('total_amount');
            
        $previousAmount = Transaction::where('store_id', $storeId)
            ->whereBetween('transaction_date', [$previousMonth, $previousMonthEnd])
            ->sum('total_amount');
        
        // Documents processed this month
        $currentDocuments = Document::where('store_id', $storeId)
            ->whereBetween('created_at', [$currentMonth, $currentMonthEnd])
            ->count();
            
        $previousDocuments = Document::where('store_id', $storeId)
            ->whereBetween('created_at', [$previousMonth, $previousMonthEnd])
            ->count();
        
        // Active vendors
        $activeVendors = Vendor::where('store_id', $storeId)
            ->where('is_active', true)
            ->count();
        
        return [
            'transactions' => [
                'current' => $currentTransactions,
                'previous' => $previousTransactions,
                'change_percent' => $this->calculatePercentChange($currentTransactions, $previousTransactions),
            ],
            'total_amount' => [
                'current' => $currentAmount,
                'previous' => $previousAmount,
                'change_percent' => $this->calculatePercentChange($currentAmount, $previousAmount),
            ],
            'documents_processed' => [
                'current' => $currentDocuments,
                'previous' => $previousDocuments,
                'change_percent' => $this->calculatePercentChange($currentDocuments, $previousDocuments),
            ],
            'active_vendors' => $activeVendors,
        ];
    }
    
    private function getChartData($storeId): array
    {
        // Last 30 days transaction trend
        $transactionTrend = Transaction::where('store_id', $storeId)
            ->where('transaction_date', '>=', now()->subDays(30))
            ->select(
                DB::raw('DATE(transaction_date) as date'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total_amount) as amount')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        // Category breakdown
        $categoryBreakdown = Transaction::where('store_id', $storeId)
            ->where('transaction_date', '>=', now()->subDays(30))
            ->join('transaction_lines', 'transactions.id', '=', 'transaction_lines.transaction_id')
            ->join('categories', 'transaction_lines.category_id', '=', 'categories.id')
            ->select(
                'categories.name',
                DB::raw('SUM(transaction_lines.total_amount) as amount')
            )
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('amount')
            ->limit(10)
            ->get();
        
        // Top vendors
        $topVendors = Transaction::where('store_id', $storeId)
            ->where('transaction_date', '>=', now()->subDays(30))
            ->whereNotNull('vendor_id')
            ->join('vendors', 'transactions.vendor_id', '=', 'vendors.id')
            ->select(
                'vendors.name',
                DB::raw('SUM(transactions.total_amount) as amount'),
                DB::raw('COUNT(*) as transaction_count')
            )
            ->groupBy('vendors.id', 'vendors.name')
            ->orderByDesc('amount')
            ->limit(10)
            ->get();
        
        return [
            'transaction_trend' => $transactionTrend,
            'category_breakdown' => $categoryBreakdown,
            'top_vendors' => $topVendors,
        ];
    }
    
    private function getRecentActivity($storeId): array
    {
        $recentTransactions = Transaction::where('store_id', $storeId)
            ->with(['vendor', 'lines.category'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'type' => 'transaction',
                    'description' => $transaction->description ?? 'Transaction',
                    'amount' => $transaction->total_amount,
                    'vendor' => $transaction->vendor?->name,
                    'date' => $transaction->transaction_date,
                    'created_at' => $transaction->created_at,
                ];
            });
        
        $recentDocuments = Document::where('store_id', $storeId)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(function ($document) {
                return [
                    'id' => $document->id,
                    'type' => 'document',
                    'description' => 'Document uploaded: ' . $document->filename,
                    'status' => $document->status,
                    'date' => $document->created_at,
                    'created_at' => $document->created_at,
                ];
            });
        
        return $recentTransactions->concat($recentDocuments)
            ->sortByDesc('created_at')
            ->take(15)
            ->values()
            ->all();
    }
    
    private function calculatePercentChange($current, $previous): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }
        
        return round((($current - $previous) / $previous) * 100, 2);
    }
}
