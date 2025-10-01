<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRoles extends ListRecords
{
    protected static string $resource = RoleResource::class;

    protected ?string $heading = 'Λίστα Ρόλων';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Νέος Ρόλος'),
        ];
    }
}
