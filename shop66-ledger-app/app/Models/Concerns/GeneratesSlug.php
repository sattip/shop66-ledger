<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * Trait GeneratesSlug
 *
 * Automatically generates unique slugs from the model's name attribute.
 * Ensures slugs are unique within the same store.
 */
trait GeneratesSlug
{
    protected static function bootGeneratesSlug(): void
    {
        static::saving(function ($model) {
            if (empty($model->slug) && ! empty($model->name)) {
                $model->slug = static::generateUniqueSlug($model);
            }
        });
    }

    /**
     * Generate a unique slug for the model within its store.
     *
     * @param  mixed  $model  The model instance
     * @return string  The generated unique slug
     */
    protected static function generateUniqueSlug($model): string
    {
        $baseSlug = Str::slug($model->name);
        $slug = $baseSlug;
        $counter = 1;

        // Build query to check for existing slugs
        $query = static::where('slug', $slug)
            ->where('store_id', $model->store_id)
            ->where('id', '!=', $model->id ?? 0);

        // Keep incrementing counter until we find a unique slug
        while ($query->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
            $query = static::where('slug', $slug)
                ->where('store_id', $model->store_id)
                ->where('id', '!=', $model->id ?? 0);
        }

        return $slug;
    }
}