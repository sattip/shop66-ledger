<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentIngestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'stage',
        'status',
        'engine',
        'message',
        'payload',
        'ocr_text',
        'metrics',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'metrics' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}
