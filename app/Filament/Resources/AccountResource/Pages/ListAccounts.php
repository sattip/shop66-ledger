<?php

namespace App\Filament\Resources\AccountResource\Pages;

use App\Filament\Resources\AccountResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAccounts extends ListRecords
{
    protected static string $resource = AccountResource::class;

    protected ?string $heading = 'Λίστα Λογαριασμών';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Νέος Λογαριασμός'),
        ];
    }
}
