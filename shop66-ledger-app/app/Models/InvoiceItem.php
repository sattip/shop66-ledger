<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'item_id',
        'description',
        'quantity',
        'unit_price',
        'discount_percent',
        'discount_amount',
        'tax_rate',
        'tax_amount',
        'total',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (InvoiceItem $invoiceItem) {
            $invoiceItem->calculateTotal();
        });
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function calculateTotal(): void
    {
        $subtotal = $this->quantity * $this->unit_price;

        if ($this->discount_percent > 0) {
            $this->discount_amount = $subtotal * ($this->discount_percent / 100);
        }

        $afterDiscount = $subtotal - $this->discount_amount;

        if ($this->tax_rate > 0) {
            $this->tax_amount = $afterDiscount * ($this->tax_rate / 100);
        }

        $this->total = $afterDiscount + $this->tax_amount;
    }
}
