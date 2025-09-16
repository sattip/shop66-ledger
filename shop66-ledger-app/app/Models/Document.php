<?php

namespace App\Models;

use App\Models\Concerns\BelongsToStore;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use BelongsToStore;
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'store_id',
        'uploaded_by',
        'vendor_id',
        'status',
        'source_type',
        'document_type',
        'document_number',
        'document_date',
        'due_date',
        'currency_code',
        'subtotal',
        'tax_total',
        'total',
        'disk',
        'path',
        'original_filename',
        'mime_type',
        'size',
        'checksum',
        'ocr_language',
        'processed_at',
        'reviewed_at',
        'extraction_payload',
        'metadata',
    ];

    protected $casts = [
        'document_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:4',
        'tax_total' => 'decimal:4',
        'total' => 'decimal:4',
        'processed_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'extraction_payload' => 'array',
        'metadata' => 'array',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(DocumentLine::class);
    }

    public function ingestions(): HasMany
    {
        return $this->hasMany(DocumentIngestion::class);
    }

    public function transaction(): HasOne
    {
        return $this->hasOne(Transaction::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}
