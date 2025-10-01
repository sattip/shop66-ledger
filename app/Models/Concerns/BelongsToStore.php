<?php

namespace App\Models\Concerns;

use App\Models\Store;
use App\Support\StoreContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToStore
{
    public static function bootBelongsToStore(): void
    {
        static::addGlobalScope('store', function (Builder $builder) {
            $storeId = app(StoreContext::class)->get();

            if ($storeId !== null) {
                $builder->where($builder->qualifyColumn('store_id'), $storeId);
            }
        });

        static::creating(function (Model $model) {
            if ($model->getAttribute('store_id') === null) {
                $storeId = app(StoreContext::class)->get();

                if ($storeId !== null) {
                    $model->setAttribute('store_id', $storeId);
                }
            }
        });
    }

    public function scopeForStore(Builder $builder, int $storeId): Builder
    {
        return $builder->withoutGlobalScope('store')
            ->where($builder->qualifyColumn('store_id'), $storeId);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
