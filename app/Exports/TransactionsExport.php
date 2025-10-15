<?php

namespace App\Exports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TransactionsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected array $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Transaction::query();

        if ($this->filters['store_id'] !== 'all') {
            $query->where('store_id', $this->filters['store_id']);
        } else {
            $query->whereIn('store_id', auth()->user()->stores->pluck('id'));
        }

        if ($this->filters['date_from']) {
            $query->where('transaction_date', '>=', $this->filters['date_from']);
        }

        if ($this->filters['date_to']) {
            $query->where('transaction_date', '<=', $this->filters['date_to']);
        }

        if ($this->filters['type'] !== 'all') {
            $query->where('type', $this->filters['type']);
        }

        if ($this->filters['category_id'] !== 'all') {
            $query->where('category_id', $this->filters['category_id']);
        }

        return $query->with(['category', 'store'])->get();
    }

    public function headings(): array
    {
        return [
            'Ημερομηνία',
            'Κατάστημα',
            'Τύπος',
            'Κατηγορία',
            'Ποσό (€)',
            'Περιγραφή',
            'Κατάσταση',
        ];
    }

    public function map($transaction): array
    {
        return [
            $transaction->transaction_date->format('d/m/Y'),
            $transaction->store->name,
            $transaction->type === 'income' ? 'Έσοδο' : 'Έξοδο',
            $transaction->category->name,
            number_format($transaction->total, 2),
            $transaction->description ?? '-',
            $transaction->status === 'posted' ? 'Καταχωρημένο' : 'Εκκρεμές',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
