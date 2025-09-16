<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'transaction_line_id',
        'item_id',
        'category_id',
        'line_number',
        'description',
        'quantity',
        'unit_price',
        'tax_rate',
        'tax_amount',
        'total',
        'confidence',
        'status',
        'metadata',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_price' => 'decimal:4',
        'tax_rate' => 'decimal:4',
        'tax_amount' => 'decimal:4',
        'total' => 'decimal:4',
        'confidence' => 'decimal:2',
        'metadata' => 'array',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function transactionLine(): BelongsTo
    {
        return $this->belongsTo(TransactionLine::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
