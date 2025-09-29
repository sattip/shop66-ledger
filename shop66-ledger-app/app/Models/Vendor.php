<?php

namespace App\Models;

use App\Models\Concerns\BelongsToStore;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Vendor extends Model
{
    use BelongsToStore;
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'store_id',
        'name',
        'slug',
        'tax_id',
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

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Vendor $vendor) {
            if (empty($vendor->slug) && ! empty($vendor->name)) {
                $baseSlug = Str::slug($vendor->name);
                $slug = $baseSlug;
                $counter = 1;

                while (static::where('slug', $slug)
                    ->where('store_id', $vendor->store_id)
                    ->where('id', '!=', $vendor->id ?? 0)
                    ->exists()) {
                    $slug = $baseSlug.'-'.$counter;
                    $counter++;
                }

                $vendor->slug = $slug;
            }
        });
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
