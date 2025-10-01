<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StoreResource\Pages;
use App\Models\Store;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StoreResource extends Resource
{
    protected static ?string $model = Store::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $navigationLabel = 'Καταστήματα';

    protected static ?string $modelLabel = 'Κατάστημα';

    protected static ?string $pluralModelLabel = 'Καταστήματα';

    protected static ?string $navigationGroup = 'Ρυθμίσεις';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Βασικές Πληροφορίες')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Όνομα Καταστήματος')
                                    ->required()
                                    ->placeholder('π.χ. Shop66 Αθήνα'),
                                Forms\Components\TextInput::make('code')
                                    ->label('Κωδικός')
                                    ->required()
                                    ->placeholder('π.χ. ATH01'),
                            ]),
                    ]),

                Forms\Components\Section::make('Στοιχεία Επικοινωνίας')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('contact_email')
                                    ->label('Email')
                                    ->email()
                                    ->placeholder('info@shop66.gr'),
                                Forms\Components\TextInput::make('contact_phone')
                                    ->label('Τηλέφωνο')
                                    ->tel()
                                    ->placeholder('+30 210 1234567'),
                            ]),
                    ]),

                Forms\Components\Section::make('Διεύθυνση')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('address_line1')
                                    ->label('Διεύθυνση')
                                    ->placeholder('Οδός και αριθμός'),
                                Forms\Components\TextInput::make('city')
                                    ->label('Πόλη')
                                    ->placeholder('Αθήνα'),
                            ]),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('postal_code')
                                    ->label('Ταχ. Κώδικας')
                                    ->placeholder('10552'),
                                Forms\Components\Hidden::make('country_code')
                                    ->default('GR'),
                            ]),
                    ]),

                Forms\Components\Hidden::make('currency_code')
                    ->default('EUR'),
                Forms\Components\Hidden::make('timezone')
                    ->default('Europe/Athens'),
                Forms\Components\Hidden::make('default_tax_rate')
                    ->default(24),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Όνομα')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('code')
                    ->label('Κωδικός')
                    ->searchable()
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('tax_id')
                    ->label('ΑΦΜ')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('contact_email')
                    ->label('Email')
                    ->searchable()
                    ->icon('heroicon-m-envelope')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('contact_phone')
                    ->label('Τηλέφωνο')
                    ->searchable()
                    ->icon('heroicon-m-phone')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('currency_code')
                    ->label('Νόμισμα')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('address_line1')
                    ->label('Διεύθυνση 1')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('address_line2')
                    ->label('Διεύθυνση 2')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('city')
                    ->label('Πόλη')
                    ->searchable(),
                Tables\Columns\TextColumn::make('state')
                    ->label('Περιοχή')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('postal_code')
                    ->label('Τ.Κ.')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('country_code')
                    ->label('Χώρα')
                    ->badge()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Δημιουργήθηκε')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Επεξεργασία'),
                Tables\Actions\DeleteAction::make()->label('Διαγραφή'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
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
            'index' => Pages\ListStores::route('/'),
            'create' => Pages\CreateStore::route('/create'),
            'edit' => Pages\EditStore::route('/{record}/edit'),
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
            $query->whereIn('id', $storeIds);
        }

        return $query;
    }
}
