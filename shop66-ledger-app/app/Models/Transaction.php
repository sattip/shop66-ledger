<?php

namespace App\Models;

use App\Models\Concerns\BelongsToStore;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use BelongsToStore;
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'store_id',
        'account_id',
        'category_id',
        'vendor_id',
        'customer_id',
        'user_id',
        'approved_by',
        'type',
        'status',
        'reference',
        'external_id',
        'transaction_date',
        'due_date',
        'currency_code',
        'exchange_rate',
        'subtotal',
        'tax_total',
        'total',
        'balance',
        'memo',
        'approved_at',
        'posted_at',
        'document_id',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'due_date' => 'date',
            'approved_at' => 'datetime',
            'posted_at' => 'datetime',
            'exchange_rate' => 'decimal:8',
            'subtotal' => 'decimal:2',
            'tax_total' => 'decimal:2',
            'total' => 'decimal:2',
            'balance' => 'decimal:2',
            'metadata' => 'array',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(TransactionLine::class);
    }
}
