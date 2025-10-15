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

        return $next($request);
    }

    /**
     * Resolve the current store from various sources in order of priority:
     * 1. Route parameter
     * 2. Header or request input
     * 3. Session (for multi-store users)
     * 4. User's first store (fallback)
     */
    private function resolveStore(Request $request): ?Store
    {
        // 1. Check route parameter
        $routeStore = $request->route('store');

        if ($routeStore instanceof Store) {
            return $routeStore;
        }

        // 2. Check header or input
        $storeId = $request->header('X-Store-ID')
            ?? $request->input('store_id');

        if ($storeId) {
            return Store::query()->whereKey($storeId)->first();
        }

        // 3. Check session for user preference
        if ($request->session()->has('current_store_id')) {
            $sessionStoreId = $request->session()->get('current_store_id');
            $user = $request->user();

            if ($user && $user->hasStoreAccess($sessionStoreId)) {
                return Store::query()->whereKey($sessionStoreId)->first();
            }
        }

        // 4. Fallback: use the first store assigned to the authenticated user
        $user = $request->user();
        if ($user) {
            $store = $user->stores()->first();

            // Store in session for future requests
            if ($store) {
                $request->session()->put('current_store_id', $store->id);
            }

            return $store;
        }

        return null;
    }
}
