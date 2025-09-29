<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected ?string $heading = 'Λίστα Χρηστών';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Νέος Χρήστης'),
        ];
    }
}
