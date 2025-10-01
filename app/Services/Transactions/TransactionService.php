<?php

namespace App\Services\Transactions;

use App\Models\Store;
use App\Models\Transaction;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    public function create(Store $store, array $data): Transaction
    {
        return DB::transaction(function () use ($store, $data) {
            $lines = $data['lines'] ?? [];
            unset($data['lines']);

            $transaction = $store->transactions()->create($data);
            $totals = $this->syncLines($transaction, $lines);

            $transaction->fill($this->totalsToAttributes($data, $totals))->save();

            return $transaction->load(['lines', 'vendor', 'customer', 'account']);
        });
    }

    public function update(Transaction $transaction, array $data): Transaction
    {
        return DB::transaction(function () use ($transaction, $data) {
            $lines = $data['lines'] ?? [];
            unset($data['lines']);

            $transaction->fill($data)->save();
            $totals = $this->syncLines($transaction, $lines, replace: true);

            $transaction->fill($this->totalsToAttributes($data, $totals))->save();

            return $transaction->load(['lines', 'vendor', 'customer', 'account']);
        });
    }

    private function syncLines(Transaction $transaction, array $lines, bool $replace = false): array
    {
        if ($replace) {
            $transaction->lines()->delete();
        }

        $subtotal = 0.0;
        $taxTotal = 0.0;
        $total = 0.0;

        $lineNumber = 1;

        foreach ($lines as $line) {
            $quantity = (float) Arr::get($line, 'quantity', 1);
            $unitPrice = (float) Arr::get($line, 'unit_price', 0);
            $discountRate = (float) Arr::get($line, 'discount_rate', 0);
            $taxRate = (float) Arr::get($line, 'tax_rate', 0);

            $lineSubtotal = $quantity * $unitPrice;
            $discountAmount = $lineSubtotal * ($discountRate / 100);
            $taxable = $lineSubtotal - $discountAmount;
            $lineTax = $taxable * ($taxRate / 100);
            $lineTotal = $taxable + $lineTax;

            $subtotal += $taxable;
            $taxTotal += $lineTax;
            $total += $lineTotal;

            $transaction->lines()->create([
                'item_id' => Arr::get($line, 'item_id'),
                'category_id' => Arr::get($line, 'category_id'),
                'line_number' => $lineNumber++,
                'description' => Arr::get($line, 'description'),
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'discount_rate' => $discountRate,
                'tax_rate' => $taxRate,
                'tax_amount' => Arr::get($line, 'tax_amount', $lineTax),
                'total' => Arr::get($line, 'total', $lineTotal),
                'metadata' => Arr::get($line, 'metadata'),
            ]);
        }

        return compact('subtotal', 'taxTotal', 'total');
    }

    private function totalsToAttributes(array $data, array $totals): array
    {
        return [
            'subtotal' => Arr::get($data, 'subtotal', $totals['subtotal']),
            'tax_total' => Arr::get($data, 'tax_total', $totals['taxTotal']),
            'total' => Arr::get($data, 'total', $totals['total']),
            'balance' => Arr::get($data, 'balance', $totals['total']),
        ];
    }
}
