<?php

namespace App\Models;

use App\Models\Concerns\BelongsToStore;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Attachment extends Model
{
    use BelongsToStore;
    use HasFactory;

    protected $fillable = [
        'store_id',
        'attachable_type',
        'attachable_id',
        'uploaded_by',
        'disk',
        'path',
        'original_filename',
        'mime_type',
        'size',
        'checksum',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
