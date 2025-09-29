<?php

namespace App\Filament\Resources\InvoiceResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Invoice Line Items';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('item_id')
                    ->label('Item')
                    ->relationship('item', 'name')
                    ->searchable()
                    ->reactive()
                    ->afterStateUpdated(function (Set $set, $state) {
                        if ($state) {
                            $item = \App\Models\Item::find($state);
                            if ($item) {
                                $set('description', $item->name);
                                $set('unit_price', $item->price ?? 0);
                            }
                        }
                    }),
                Forms\Components\TextInput::make('description')
                    ->label('Description')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Grid::make(4)
                    ->schema([
                        Forms\Components\TextInput::make('quantity')
                            ->label('Quantity')
                            ->numeric()
                            ->default(1)
                            ->required()
                            ->reactive(),
                        Forms\Components\TextInput::make('unit_price')
                            ->label('Unit Price')
                            ->numeric()
                            ->prefix('$')
                            ->required()
                            ->reactive(),
                        Forms\Components\TextInput::make('discount_percent')
                            ->label('Discount %')
                            ->numeric()
                            ->suffix('%')
                            ->default(0)
                            ->reactive(),
                        Forms\Components\TextInput::make('tax_rate')
                            ->label('Tax %')
                            ->numeric()
                            ->suffix('%')
                            ->default(0)
                            ->reactive(),
                    ]),
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\TextInput::make('discount_amount')
                            ->label('Discount Amount')
                            ->numeric()
                            ->prefix('$')
                            ->disabled()
                            ->dehydrated(),
                        Forms\Components\TextInput::make('tax_amount')
                            ->label('Tax Amount')
                            ->numeric()
                            ->prefix('$')
                            ->disabled()
                            ->dehydrated(),
                        Forms\Components\TextInput::make('total')
                            ->label('Line Total')
                            ->numeric()
                            ->prefix('$')
                            ->disabled()
                            ->dehydrated(),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->columns([
                Tables\Columns\TextColumn::make('description')
                    ->searchable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('unit_price')
                    ->money()
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('discount_amount')
                    ->money()
                    ->alignEnd()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('tax_amount')
                    ->money()
                    ->alignEnd()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('total')
                    ->money()
                    ->alignEnd()
                    ->weight('bold'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $invoiceItem = new \App\Models\InvoiceItem($data);
                        $invoiceItem->calculateTotal();

                        return $invoiceItem->toArray();
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $invoiceItem = new \App\Models\InvoiceItem($data);
                        $invoiceItem->calculateTotal();

                        return $invoiceItem->toArray();
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->reorderable('sort_order');
    }

    public function isReadOnly(): bool
    {
        return $this->ownerRecord->invoice_type === 'simple';
    }
}
