<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PermissionResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\Permission\Models\Permission;

class PermissionResource extends Resource
{
    protected static ?string $model = Permission::class;

    protected static ?string $navigationIcon = 'heroicon-o-key';

    protected static ?string $navigationGroup = 'Διαχείριση';

    protected static ?string $navigationLabel = 'Δικαιώματα';

    protected static ?string $modelLabel = 'Δικαίωμα';

    protected static ?string $pluralModelLabel = 'Δικαιώματα';

    protected static ?int $navigationSort = 3;

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
                Forms\Components\Section::make('Στοιχεία Δικαιώματος')
                    ->schema([
                        Forms\Components\Grid::make(1)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Όνομα Δικαιώματος')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->placeholder('π.χ. manage-invoices')
                                    ->helperText('Χρησιμοποιήστε μικρά γράμματα και παύλες')
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('description')
                                    ->label('Περιγραφή')
                                    ->placeholder('Περιγράψτε τι επιτρέπει αυτό το δικαίωμα')
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ]),
                    ]),

                Forms\Components\Section::make('Ανάθεση σε Ρόλους')
                    ->schema([
                        Forms\Components\CheckboxList::make('roles')
                            ->label('Ρόλοι με αυτό το Δικαίωμα')
                            ->relationship('roles', 'name')
                            ->columns(2)
                            ->searchable()
                            ->bulkToggleable()
                            ->helperText('Επιλέξτε τους ρόλους που θα έχουν αυτό το δικαίωμα'),
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
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Το όνομα αντιγράφηκε')
                    ->copyMessageDuration(1500),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Ρόλοι')
                    ->badge()
                    ->separator(',')
                    ->searchable(),
                Tables\Columns\TextColumn::make('users_count')
                    ->label('Χρήστες')
                    ->getStateUsing(function (Permission $record): int {
                        return $record->users()->count() +
                               $record->roles()->withCount('users')->get()->sum('users_count');
                    })
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Δημιουργήθηκε')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                    ->label('Ρόλος')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Επεξεργασία'),
                Tables\Actions\DeleteAction::make()->label('Διαγραφή'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->defaultSort('name', 'asc');
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
            'index' => Pages\ListPermissions::route('/'),
            'create' => Pages\CreatePermission::route('/create'),
            'edit' => Pages\EditPermission::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }
}
