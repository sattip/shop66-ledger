<?php

namespace App\Providers;

use App\Support\StoreContext;
use Illuminate\Support\ServiceProvider;

class StoreContextServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(StoreContext::class, fn () => new StoreContext);
    }
}
