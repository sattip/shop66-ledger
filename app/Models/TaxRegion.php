<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaxRegion extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'country_code',
        'region',
        'default_rate',
        'settings',
    ];

    protected $casts = [
        'default_rate' => 'decimal:4',
        'settings' => 'array',
    ];

    public function stores(): HasMany
    {
        return $this->hasMany(Store::class);
    }
}
