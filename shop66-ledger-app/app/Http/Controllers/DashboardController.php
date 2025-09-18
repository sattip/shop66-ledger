<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Account;
use App\Models\Vendor;
use App\Models\Item;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = $this->getDashboardStats();
        $chartData = $this->getChartData();
        $recentTransactions = $this->getRecentTransactions();
        $pendingDocuments = $this->getPendingDocuments();

        return view('dashboard', compact(
            'stats',
            'chartData', 
            'recentTransactions',
            'pendingDocuments'
        ));
    }

    private function getDashboardStats()
    {
        return [
            'total_transactions' => Transaction::count(),
            'total_income' => Transaction::where('type', 'income')->sum('total'),
            'total_expenses' => Transaction::where('type', 'expense')->sum('total'),
            'pending_documents' => \App\Models\Document::where('status', 'pending_review')->count(),
        ];
    }

    private function getChartData()
    {
        // Get last 12 months of data
        $monthlyData = Transaction::select(
                DB::raw('DATE_FORMAT(transaction_date, "%Y-%m") as month'),
                DB::raw('SUM(CASE WHEN type = "income" THEN total ELSE 0 END) as income'),
                DB::raw('SUM(CASE WHEN type = "expense" THEN total ELSE 0 END) as expenses')
            )
            ->where('transaction_date', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $labels = $monthlyData->pluck('month')->map(function($month) {
            return date('M Y', strtotime($month . '-01'));
        })->toArray();

        $categoryData = Transaction::join('transaction_lines', 'transactions.id', '=', 'transaction_lines.transaction_id')
            ->join('categories', 'transaction_lines.category_id', '=', 'categories.id')
            ->where('transactions.type', 'expense')
            ->select('categories.name', DB::raw('SUM(transaction_lines.total) as total'))
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        return [
            'monthly' => [
                'labels' => $labels,
                'income' => $monthlyData->pluck('income')->toArray(),
                'expenses' => $monthlyData->pluck('expenses')->toArray(),
            ],
            'categories' => [
                'labels' => $categoryData->pluck('name')->toArray(),
                'values' => $categoryData->pluck('total')->toArray(),
            ]
        ];
    }

    private function getRecentTransactions()
    {
        return Transaction::with(['account', 'vendor'])
            ->orderByDesc('transaction_date')
            ->limit(5)
            ->get();
    }

    private function getPendingDocuments()
    {
        return \App\Models\Document::where('status', 'pending_review')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();
    }
}