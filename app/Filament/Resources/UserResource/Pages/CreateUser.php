<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Support\StoreContext;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Spatie\Permission\PermissionRegistrar;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label('Δημιουργία');
    }

    protected function getCreateAnotherFormAction(): Action
    {
        return parent::getCreateAnotherFormAction()
            ->label('Δημιουργία & δημιουργία άλλου');
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->label('Ακύρωση');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Extract permissions and roles to handle them separately with store context
        $this->permissions = $data['permissions'] ?? [];
        $this->roles = $data['roles'] ?? [];

        // Remove from data so they don't get processed by default relationship handling
        unset($data['permissions'], $data['roles']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $user = $this->record;
        $storeContext = app(StoreContext::class);
        $storeId = $storeContext->get();

        // If no store context, use the first store of the user
        if (! $storeId && $user->stores()->exists()) {
            $storeId = $user->stores()->first()->id;
        }

        if ($storeId) {
            // Set the team/store context for permissions
            app(PermissionRegistrar::class)->setPermissionsTeamId($storeId);

            // Sync roles with store context (convert IDs to names)
            if (isset($this->roles)) {
                $roleNames = \Spatie\Permission\Models\Role::whereIn('id', $this->roles)->pluck('name')->toArray();
                $user->syncRoles($roleNames);
            }

            // Sync permissions with store context (convert IDs to names)
            if (isset($this->permissions)) {
                $permissionNames = \Spatie\Permission\Models\Permission::whereIn('id', $this->permissions)->pluck('name')->toArray();
                $user->syncPermissions($permissionNames);
            }
        }
    }
}
