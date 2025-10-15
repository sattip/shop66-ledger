<?php

namespace App\Support;

use Spatie\Permission\PermissionRegistrar;

class StoreContext
{
    public function set(?int $storeId): void
    {
        session(['current_store_id' => $storeId]);

        if (app()->bound(PermissionRegistrar::class)) {
            app(PermissionRegistrar::class)->setPermissionsTeamId($storeId);
        }
    }

    public function get(): ?int
    {
        return session('current_store_id');
    }

    public function clear(): void
    {
        session()->forget('current_store_id');
    }
}
