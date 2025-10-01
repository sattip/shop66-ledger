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

    /**
     * Calculate the total price for this line item.
     * Formula: Total = (Quantity × Unit Price - Discount) + Tax
     * All values are rounded to 2 decimal places for currency accuracy.
     */
    public function calculateTotal(): void
    {
        // Ensure all values are non-negative
        $this->quantity = max(0, $this->quantity ?? 0);
        $this->unit_price = max(0, $this->unit_price ?? 0);
        $this->discount_percent = max(0, min(100, $this->discount_percent ?? 0));
        $this->tax_rate = max(0, $this->tax_rate ?? 0);

        // Calculate subtotal (quantity × unit price)
        $subtotal = $this->quantity * $this->unit_price;

        // Calculate discount amount
        if ($this->discount_percent > 0) {
            $this->discount_amount = round($subtotal * ($this->discount_percent / 100), 2);
        } else {
            // If no discount percentage, ensure discount_amount is not negative
            $this->discount_amount = max(0, $this->discount_amount ?? 0);
        }

        // Ensure discount doesn't exceed subtotal
        $this->discount_amount = min($this->discount_amount, $subtotal);

        // Calculate amount after discount
        $afterDiscount = $subtotal - $this->discount_amount;

        // Calculate tax amount on discounted price
        if ($this->tax_rate > 0) {
            $this->tax_amount = round($afterDiscount * ($this->tax_rate / 100), 2);
        } else {
            $this->tax_amount = $this->tax_amount ?? 0;
        }

        // Calculate final total and round to 2 decimal places
        $this->total = round($afterDiscount + $this->tax_amount, 2);
    }
}
