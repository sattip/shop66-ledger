<?php

namespace App\Services\Reports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportExport implements FromArray, WithHeadings, WithStyles
{
    private array $data;
    private string $type;

    public function __construct(array $data, string $type)
    {
        $this->data = $data;
        $this->type = $type;
    }

    public function array(): array
    {
        switch ($this->type) {
            case 'financial_summary':
                return $this->formatFinancialSummary();
            case 'vendor':
                return $this->formatVendorReport();
            case 'category':
                return $this->formatCategoryReport();
            default:
                return [];
        }
    }

    public function headings(): array
    {
        switch ($this->type) {
            case 'financial_summary':
                return ['Category/Vendor', 'Amount', 'Transaction Count'];
            case 'vendor':
                return ['Vendor Name', 'Total Amount', 'Transaction Count', 'Status'];
            case 'category':
                return ['Category Name', 'Type', 'Total Amount', 'Transaction Count'];
            default:
                return [];
        }
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    private function formatFinancialSummary(): array
    {
        $rows = [];
        
        // Summary section
        $rows[] = ['Total Income', $this->data['summary']['total_income'], ''];
        $rows[] = ['Total Expenses', $this->data['summary']['total_expenses'], ''];
        $rows[] = ['Net Income', $this->data['summary']['net_income'], ''];
        $rows[] = ['', '', ''];
        
        // Category breakdown
        foreach ($this->data['category_breakdown'] as $category => $data) {
            $rows[] = [$category, $data['amount'], $data['count']];
        }
        
        return $rows;
    }

    private function formatVendorReport(): array
    {
        $rows = [];
        
        foreach ($this->data as $vendor) {
            $rows[] = [
                $vendor['name'],
                $vendor['transactions_sum_total_amount'] ?? 0,
                $vendor['transactions_count'] ?? 0,
                $vendor['is_active'] ? 'Active' : 'Inactive',
            ];
        }
        
        return $rows;
    }

    private function formatCategoryReport(): array
    {
        $rows = [];
        
        foreach ($this->data as $category) {
            $rows[] = [
                $category->name,
                $category->type,
                $category->total_amount,
                $category->transaction_count,
            ];
        }
        
        return $rows;
    }
}