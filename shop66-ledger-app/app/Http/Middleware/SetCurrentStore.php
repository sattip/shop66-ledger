<?php

namespace App\Http\Middleware;

use App\Models\Store;
use App\Support\StoreContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetCurrentStore
{
    public function __construct(private StoreContext $context) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $store = $this->resolveStore($request);

        if ($store) {
            $this->context->set($store->getKey());
            $request->attributes->set('current_store', $store);
        }

        try {
            return $next($request);
        } finally {
            $this->context->clear();
        }
    }

    private function resolveStore(Request $request): ?Store
    {
        $routeStore = $request->route('store');

        if ($routeStore instanceof Store) {
            return $routeStore;
        }

        $storeId = $request->header('X-Store-ID')
            ?? $request->input('store_id');

        if ($storeId) {
            return Store::query()->whereKey($storeId)->first();
        }

        // Fallback: use the first store assigned to the authenticated user
        $user = $request->user();
        if ($user) {
            return $user->stores()->first();
        }

        return null;
    }
}
