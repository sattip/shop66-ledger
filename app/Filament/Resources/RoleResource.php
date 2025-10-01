<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\Permission\Models\Role;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'Διαχείριση';

    protected static ?string $navigationLabel = 'Ρόλοι';

    protected static ?string $modelLabel = 'Ρόλος';

    protected static ?string $pluralModelLabel = 'Ρόλοι';

    protected static ?int $navigationSort = 2;

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        return $user && $user->hasAnyRoleValue([
            \App\Enums\UserRole::ADMIN,
        ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Στοιχεία Ρόλου')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Όνομα Ρόλου')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->placeholder('π.χ. Διαχειριστής, Υπάλληλος')
                            ->helperText('Δώστε ένα περιγραφικό όνομα για τον ρόλο')
                            ->maxLength(255),
                    ]),
                Forms\Components\Section::make('Δικαιώματα')
                    ->schema([
                        Forms\Components\CheckboxList::make('permissions')
                            ->label('Δικαιώματα Ρόλου')
                            ->relationship('permissions', 'name')
                            ->columns(3)
                            ->searchable()
                            ->bulkToggleable()
                            ->helperText('Επιλέξτε τα δικαιώματα που θα έχει αυτός ο ρόλος'),
                    ]),
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
                Tables\Columns\TextColumn::make('permissions_count')
                    ->label('Δικαιώματα')
                    ->counts('permissions')
                    ->badge(),
                Tables\Columns\TextColumn::make('users_count')
                    ->label('Χρήστες')
                    ->counts('users')
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Δημιουργήθηκε')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Επεξεργασία'),
                Tables\Actions\DeleteAction::make()->label('Διαγραφή'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
