<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Διαχείριση';

    protected static ?string $navigationLabel = 'Χρήστες';

    protected static ?string $modelLabel = 'Χρήστης';

    protected static ?string $pluralModelLabel = 'Χρήστες';

    protected static ?int $navigationSort = 1;

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
                Forms\Components\Section::make('Στοιχεία Χρήστη')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Όνομα')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255),
                            ]),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('password')
                                    ->label('Κωδικός')
                                    ->password()
                                    ->maxLength(255)
                                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                                    ->dehydrated(fn ($state) => filled($state))
                                    ->required(fn (string $context): bool => $context === 'create'),
                                Forms\Components\DateTimePicker::make('email_verified_at')
                                    ->label('Email Επιβεβαιώθηκε'),
                            ]),
                    ]),

                Forms\Components\Section::make('Ρόλοι & Δικαιώματα')
                    ->schema([
                        Forms\Components\CheckboxList::make('roles')
                            ->label('Ρόλοι')
                            ->options(fn () => \Spatie\Permission\Models\Role::pluck('name', 'id')->toArray())
                            ->default(fn ($record) => $record?->roles->pluck('id')->toArray() ?? [])
                            ->columns(2)
                            ->disabled(fn () => ! auth()->user()->hasAnyRoleValue([\App\Enums\UserRole::ADMIN]))
                            ->helperText(fn () => auth()->user()->hasAnyRoleValue([\App\Enums\UserRole::ADMIN])
                                ? 'Επιλέξτε τους ρόλους του χρήστη'
                                : 'Μόνο διαχειριστές μπορούν να ορίσουν ρόλους'
                            ),
                        Forms\Components\CheckboxList::make('permissions')
                            ->label('Ειδικά Δικαιώματα')
                            ->options(fn () => \Spatie\Permission\Models\Permission::pluck('name', 'id')->toArray())
                            ->default(fn ($record) => $record?->permissions->pluck('id')->toArray() ?? [])
                            ->columns(2)
                            ->searchable()
                            ->bulkToggleable()
                            ->disabled(fn () => ! auth()->user()->hasAnyRoleValue([\App\Enums\UserRole::ADMIN]))
                            ->helperText(fn () => auth()->user()->hasAnyRoleValue([\App\Enums\UserRole::ADMIN])
                                ? 'Επιπλέον δικαιώματα εκτός των ρόλων. Χρησιμοποιήστε "Select All" για να επιλέξετε όλα.'
                                : 'Μόνο διαχειριστές μπορούν να ορίσουν δικαιώματα'
                            ),
                    ]),

                Forms\Components\Section::make('Καταστήματα')
                    ->schema([
                        Forms\Components\CheckboxList::make('stores')
                            ->label('Πρόσβαση σε Καταστήματα')
                            ->relationship('stores', 'name')
                            ->columns(2)
                            ->helperText('Επιλέξτε τα καταστήματα στα οποία έχει πρόσβαση ο χρήστης'),
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
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Ρόλοι')
                    ->badge()
                    ->separator(','),
                Tables\Columns\IconColumn::make('email_verified_at')
                    ->label('Email Επιβεβαιώθηκε')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),
                Tables\Columns\TextColumn::make('stores.name')
                    ->label('Καταστήματα')
                    ->badge()
                    ->separator(',')
                    ->toggleable(),
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
                Tables\Filters\Filter::make('verified')
                    ->label('Email Επιβεβαιωμένο')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('email_verified_at')),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // Filter users: show only users who share at least one store with current user
        $user = auth()->user();
        if ($user) {
            $storeIds = $user->stores()->pluck('stores.id');
            $query->whereHas('stores', function ($query) use ($storeIds) {
                $query->whereIn('stores.id', $storeIds);
            });
        }

        return $query;
    }
}
