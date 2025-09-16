<?php

namespace App\Services\Reports;

use App\Models\Store;
use App\Models\Transaction;
use App\Models\Vendor;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportsService
{
    public function getFinancialSummary(Store $store, string $startDate, string $endDate): array
    {
        $transactions = Transaction::where('store_id', $store->id)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->with(['vendor', 'lines.category'])
            ->get();

        $totalIncome = $transactions->where('type', 'income')->sum('total_amount');
        $totalExpenses = $transactions->where('type', 'expense')->sum('total_amount');
        $netIncome = $totalIncome - $totalExpenses;

        // Category breakdown
        $categoryBreakdown = $transactions
            ->flatMap(function ($transaction) {
                return $transaction->lines;
            })
            ->groupBy('category.name')
            ->map(function ($lines) {
                return [
                    'amount' => $lines->sum('total_amount'),
                    'count' => $lines->count(),
                ];
            })
            ->sortByDesc('amount');

        // Vendor breakdown
        $vendorBreakdown = $transactions
            ->whereNotNull('vendor')
            ->groupBy('vendor.name')
            ->map(function ($transactions) {
                return [
                    'amount' => $transactions->sum('total_amount'),
                    'count' => $transactions->count(),
                ];
            })
            ->sortByDesc('amount');

        return [
            'summary' => [
                'total_income' => $totalIncome,
                'total_expenses' => $totalExpenses,
                'net_income' => $netIncome,
                'transaction_count' => $transactions->count(),
            ],
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'category_breakdown' => $categoryBreakdown,
            'vendor_breakdown' => $vendorBreakdown,
        ];
    }

    public function getVendorReport(Store $store, string $startDate, string $endDate): Collection
    {
        return Vendor::where('store_id', $store->id)
            ->withCount(['transactions' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('transaction_date', [$startDate, $endDate]);
            }])
            ->withSum(['transactions' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('transaction_date', [$startDate, $endDate]);
            }], 'total_amount')
            ->where('is_active', true)
            ->orderByDesc('transactions_sum_total_amount')
            ->get();
    }

    public function getCategoryReport(Store $store, string $startDate, string $endDate): Collection
    {
        return DB::table('categories')
            ->where('store_id', $store->id)
            ->leftJoin('transaction_lines', 'categories.id', '=', 'transaction_lines.category_id')
            ->leftJoin('transactions', function ($join) use ($startDate, $endDate) {
                $join->on('transaction_lines.transaction_id', '=', 'transactions.id')
                     ->whereBetween('transactions.transaction_date', [$startDate, $endDate]);
            })
            ->select(
                'categories.name',
                'categories.type',
                DB::raw('COUNT(transaction_lines.id) as transaction_count'),
                DB::raw('COALESCE(SUM(transaction_lines.total_amount), 0) as total_amount')
            )
            ->groupBy('categories.id', 'categories.name', 'categories.type')
            ->orderByDesc('total_amount')
            ->get();
    }

    public function exportToExcel(array $data, string $type): string
    {
        $filename = $type . '_report_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
        
        Excel::store(new ReportExport($data, $type), $filename, 'local');
        
        return $filename;
    }

    public function exportToPdf(array $data, string $type): string
    {
        $filename = $type . '_report_' . now()->format('Y-m-d_H-i-s') . '.pdf';
        
        $pdf = Pdf::loadView('reports.' . $type, compact('data'))
                  ->setPaper('a4', 'portrait');
        
        $pdf->save(storage_path('app/' . $filename));
        
        return $filename;
    }
}