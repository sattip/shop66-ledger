<?php

namespace App\Filament\Resources\PermissionResource\Pages;

use App\Filament\Resources\PermissionResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditPermission extends EditRecord
{
    protected static string $resource = PermissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['guard_name'] = 'web';

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['guard_name'] = 'web';

        return $data;
    }

    protected function getSaveFormAction(): Action
    {
        return Action::make('save')
            ->label('Αποθήκευση αλλαγών')
            ->submit('save')
            ->keyBindings(['mod+s']);
    }

    protected function getCancelFormAction(): Action
    {
        return Action::make('cancel')
            ->label('Ακύρωση')
            ->url($this->getResource()::getUrl('index'))
            ->color('gray');
    }
}
