<?php

namespace App\Support;

use Spatie\Permission\DefaultTeamResolver;

class StoreTeamResolver extends DefaultTeamResolver
{
    public function getPermissionsTeamId(): int|string|null
    {
        $teamId = parent::getPermissionsTeamId();

        if ($teamId !== null) {
            return $teamId;
        }

        return app(StoreContext::class)->get();
    }
}
