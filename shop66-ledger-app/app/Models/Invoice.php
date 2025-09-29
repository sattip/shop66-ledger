<?php

namespace App\Models;

use App\Models\Concerns\BelongsToStore;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use BelongsToStore;
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'store_id',
        'vendor_id',
        'invoice_number',
        'invoice_date',
        'due_date',
        'invoice_type',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'status',
        'notes',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'due_date' => 'date',
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Invoice $invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = self::generateInvoiceNumber($invoice->store_id);
            }
        });

        static::saving(function (Invoice $invoice) {
            if ($invoice->invoice_type === 'simple') {
                $invoice->subtotal = $invoice->total_amount - $invoice->tax_amount + $invoice->discount_amount;
            }
        });
    }

    public static function generateInvoiceNumber(int $storeId): string
    {
        return \DB::transaction(function () use ($storeId) {
            $prefix = 'INV-'.date('Y');
            $lastInvoice = self::where('store_id', $storeId)
                ->where('invoice_number', 'like', $prefix.'%')
                ->orderBy('invoice_number', 'desc')
                ->lockForUpdate()
                ->first();

            if ($lastInvoice) {
                $lastNumber = (int) substr($lastInvoice->invoice_number, -5);
                $newNumber = $lastNumber + 1;
            } else {
                $newNumber = 1;
            }

            return $prefix.'-'.str_pad($newNumber, 5, '0', STR_PAD_LEFT);
        });
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('sort_order');
    }

    public function isSimple(): bool
    {
        return $this->invoice_type === 'simple';
    }

    public function isDetailed(): bool
    {
        return $this->invoice_type === 'detailed';
    }

    public function calculateTotals(): void
    {
        if ($this->isDetailed()) {
            $this->subtotal = $this->items->sum('total');
            $this->tax_amount = $this->items->sum('tax_amount');
            $this->discount_amount = $this->items->sum('discount_amount');
            $this->total_amount = $this->subtotal + $this->tax_amount - $this->discount_amount;
        }
    }
}
