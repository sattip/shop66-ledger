<?php

namespace App\Models;

use App\Models\Concerns\BelongsToStore;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Customer extends Model
{
    use BelongsToStore;
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'store_id',
        'name',
        'slug',
        'email',
        'phone',
        'address',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Customer $customer) {
            if (empty($customer->slug) && ! empty($customer->name)) {
                $baseSlug = Str::slug($customer->name);
                $slug = $baseSlug;
                $counter = 1;

                while (static::where('slug', $slug)
                    ->where('store_id', $customer->store_id)
                    ->where('id', '!=', $customer->id ?? 0)
                    ->exists()) {
                    $slug = $baseSlug.'-'.$counter;
                    $counter++;
                }

                $customer->slug = $slug;
            }
        });
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
