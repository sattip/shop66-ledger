<?php

namespace App\Filament\Resources\ItemResource\Pages;

use App\Filament\Resources\ItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListItems extends ListRecords
{
    protected static string $resource = ItemResource::class;

    protected ?string $heading = 'Λίστα Ειδών';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Νέο Είδος'),
        ];
    }
}
