<?php

namespace App\Models;

use App\Models\Concerns\BelongsToStore;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use BelongsToStore;
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'store_id',
        'name',
        'slug',
        'customer_code',
        'email',
        'phone',
        'website',
        'currency_code',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'postal_code',
        'country_code',
        'notes',
        'metadata',
        'is_active',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_active' => 'boolean',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
