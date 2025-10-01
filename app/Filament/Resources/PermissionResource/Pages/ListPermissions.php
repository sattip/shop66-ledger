<?php

namespace App\Filament\Resources\PermissionResource\Pages;

use App\Filament\Resources\PermissionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPermissions extends ListRecords
{
    protected static string $resource = PermissionResource::class;

    protected ?string $heading = 'Λίστα Δικαιωμάτων';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Νέο Δικαίωμα'),
        ];
    }
}
