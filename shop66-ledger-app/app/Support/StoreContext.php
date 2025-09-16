<?php

namespace App\Support;

use Spatie\Permission\PermissionRegistrar;

class StoreContext
{
    private ?int $storeId = null;

    public function set(?int $storeId): void
    {
        $this->storeId = $storeId;

        if (app()->bound(PermissionRegistrar::class)) {
            app(PermissionRegistrar::class)->setPermissionsTeamId($storeId);
        }
    }

    public function get(): ?int
    {
        return $this->storeId;
    }

    public function clear(): void
    {
        $this->set(null);
    }
}
