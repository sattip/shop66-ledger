<?php

namespace App\Http\Controllers\Api\Concerns;

use App\Enums\UserRole;
use App\Models\Store;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

trait AuthorizesStoreAccess
{
    protected function authorizeStore(Request $request, Store $store): void
    {
        $user = $request->user();

        if (! $user) {
            throw new AccessDeniedHttpException('Unauthenticated.');
        }

        if ($user->hasAnyRoleValue([UserRole::SUPER_ADMIN, UserRole::ADMIN])) {
            return;
        }

        $hasStore = $user->stores()->whereKey($store->id)->exists();

        if (! $hasStore) {
            throw new AccessDeniedHttpException('You do not have access to this store.');
        }
    }
}
