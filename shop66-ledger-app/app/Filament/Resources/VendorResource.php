<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VendorResource\Pages;
use App\Models\Vendor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VendorResource extends Resource
{
    protected static ?string $model = Vendor::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationGroup = 'Πελάτες & Προμηθευτές';

    protected static ?string $navigationLabel = 'Προμηθευτές';

    protected static ?string $modelLabel = 'Προμηθευτής';

    protected static ?string $pluralModelLabel = 'Προμηθευτές';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('store_id')
                    ->relationship('store', 'name')
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\TextInput::make('slug')
                    ->required(),
                Forms\Components\TextInput::make('tax_id'),
                Forms\Components\TextInput::make('email')
                    ->email(),
                Forms\Components\TextInput::make('phone')
                    ->tel(),
                Forms\Components\TextInput::make('website'),
                Forms\Components\TextInput::make('currency_code'),
                Forms\Components\TextInput::make('address_line1'),
                Forms\Components\TextInput::make('address_line2'),
                Forms\Components\TextInput::make('city'),
                Forms\Components\TextInput::make('state'),
                Forms\Components\TextInput::make('postal_code'),
                Forms\Components\TextInput::make('country_code'),
                Forms\Components\Textarea::make('notes')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('metadata')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_active')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('store.name')
                    ->label('Κατάστημα')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Όνομα')
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tax_id')
                    ->label('ΑΦΜ')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Τηλέφωνο')
                    ->searchable(),
                Tables\Columns\TextColumn::make('website')
                    ->label('Ιστοσελίδα')
                    ->searchable(),
                Tables\Columns\TextColumn::make('currency_code')
                    ->label('Νόμισμα')
                    ->searchable(),
                Tables\Columns\TextColumn::make('address_line1')
                    ->label('Διεύθυνση 1')
                    ->searchable(),
                Tables\Columns\TextColumn::make('address_line2')
                    ->label('Διεύθυνση 2')
                    ->searchable(),
                Tables\Columns\TextColumn::make('city')
                    ->label('Πόλη')
                    ->searchable(),
                Tables\Columns\TextColumn::make('state')
                    ->label('Περιοχή')
                    ->searchable(),
                Tables\Columns\TextColumn::make('postal_code')
                    ->label('Τ.Κ.')
                    ->searchable(),
                Tables\Columns\TextColumn::make('country_code')
                    ->label('Χώρα')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Ενεργό')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make()
                    ->label('Διαγραμμένα'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Επεξεργασία'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Διαγραφή'),
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->label('Οριστική Διαγραφή'),
                    Tables\Actions\RestoreBulkAction::make()
                        ->label('Επαναφορά'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVendors::route('/'),
            'create' => Pages\CreateVendor::route('/create'),
            'edit' => Pages\EditVendor::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);

        // Filter by user's assigned stores
        $user = auth()->user();
        if ($user) {
            $storeIds = $user->stores()->pluck('stores.id');
            $query->whereIn('store_id', $storeIds);
        }

        return $query;
    }
}
