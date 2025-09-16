<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Store extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'tax_region_id',
        'name',
        'code',
        'currency_code',
        'timezone',
        'tax_id',
        'contact_email',
        'contact_phone',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'postal_code',
        'country_code',
        'default_tax_rate',
        'settings',
    ];

    protected $casts = [
        'default_tax_rate' => 'decimal:4',
        'settings' => 'array',
    ];

    public function taxRegion(): BelongsTo
    {
        return $this->belongsTo(TaxRegion::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps()->withPivot('role');
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function vendors(): HasMany
    {
        return $this->hasMany(Vendor::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
