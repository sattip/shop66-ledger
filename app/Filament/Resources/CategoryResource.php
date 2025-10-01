<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use App\Models\Store;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Ρυθμίσεις';

    protected static ?string $navigationLabel = 'Κατηγορίες';

    protected static ?string $modelLabel = 'Κατηγορία';

    protected static ?string $pluralModelLabel = 'Κατηγορίες';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Στοιχεία Κατηγορίας')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('store_id')
                                    ->label('Κατάστημα')
                                    ->options(function () {
                                        $user = auth()->user();
                                        if (! $user) {
                                            return [];
                                        }

                                        return $user->stores()->pluck('stores.name', 'stores.id');
                                    })
                                    ->required()
                                    ->searchable()
                                    ->default(fn () => auth()->user()?->stores()->first()?->id)
                                    ->reactive()
                                    ->helperText('Επιλέξτε κατάστημα από τα καταστήματά σας'),

                                Forms\Components\Select::make('parent_id')
                                    ->label('Γονική Κατηγορία')
                                    ->options(function (Get $get) {
                                        $storeId = $get('store_id');
                                        if (! $storeId) {
                                            return [];
                                        }

                                        return Category::where('store_id', $storeId)
                                            ->whereNull('parent_id')
                                            ->pluck('name', 'id');
                                    })
                                    ->searchable()
                                    ->placeholder('Καμία (Κύρια Κατηγορία)')
                                    ->helperText('Αφήστε κενό για κύρια κατηγορία')
                                    ->disabled(fn (Get $get) => ! $get('store_id')),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Όνομα Κατηγορίας')
                                    ->required()
                                    ->maxLength(255)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                                        if ($state) {
                                            $set('slug', Str::slug($state));
                                        }
                                    })
                                    ->placeholder('π.χ. Είδη Παντοπωλείου'),

                                Forms\Components\TextInput::make('slug')
                                    ->label('Slug (URL)')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->helperText('Χρησιμοποιείται στη διεύθυνση URL'),
                            ]),

                        Forms\Components\Select::make('type')
                            ->label('Τύπος Κατηγορίας')
                            ->options([
                                'income' => 'Έσοδα',
                                'expense' => 'Έξοδα',
                                'product' => 'Προϊόντα',
                                'service' => 'Υπηρεσίες',
                                'other' => 'Άλλο',
                            ])
                            ->required()
                            ->default('expense')
                            ->helperText('Επιλέξτε τον τύπο της κατηγορίας'),

                        Forms\Components\Textarea::make('description')
                            ->label('Περιγραφή')
                            ->rows(3)
                            ->maxLength(500)
                            ->placeholder('Προαιρετική περιγραφή της κατηγορίας')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Ρυθμίσεις')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Toggle::make('is_active')
                                    ->label('Ενεργή')
                                    ->default(true)
                                    ->helperText('Οι ανενεργές κατηγορίες δεν εμφανίζονται στις επιλογές'),

                                Forms\Components\Toggle::make('is_system')
                                    ->label('Κατηγορία Συστήματος')
                                    ->default(false)
                                    ->disabled()
                                    ->helperText('Οι κατηγορίες συστήματος δεν μπορούν να διαγραφούν'),

                                Forms\Components\TextInput::make('display_order')
                                    ->label('Σειρά Εμφάνισης')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->helperText('Μικρότερος αριθμός = υψηλότερη προτεραιότητα'),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('store.name')
                    ->label('Κατάστημα')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Όνομα')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('parent.name')
                    ->label('Γονική Κατηγορία')
                    ->placeholder('Κύρια Κατηγορία')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('full_path')
                    ->label('Πλήρης Διαδρομή')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where('name', 'like', "%{$search}%");
                    })
                    ->wrap()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('type')
                    ->label('Τύπος')
                    ->badge()
                    ->colors([
                        'success' => 'income',
                        'danger' => 'expense',
                        'info' => 'product',
                        'warning' => 'service',
                        'gray' => 'other',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'income' => 'Έσοδα',
                        'expense' => 'Έξοδα',
                        'product' => 'Προϊόντα',
                        'service' => 'Υπηρεσίες',
                        'other' => 'Άλλο',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Ενεργή')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\IconColumn::make('is_system')
                    ->label('Συστήματος')
                    ->boolean()
                    ->trueIcon('heroicon-o-lock-closed')
                    ->falseIcon('heroicon-o-lock-open')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('display_order')
                    ->label('Σειρά')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Δημιουργήθηκε')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('store_id')
                    ->label('Κατάστημα')
                    ->relationship('store', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('type')
                    ->label('Τύπος')
                    ->options([
                        'income' => 'Έσοδα',
                        'expense' => 'Έξοδα',
                        'product' => 'Προϊόντα',
                        'service' => 'Υπηρεσίες',
                        'other' => 'Άλλο',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Κατάσταση')
                    ->placeholder('Όλες')
                    ->trueLabel('Ενεργές')
                    ->falseLabel('Ανενεργές'),

                Tables\Filters\TernaryFilter::make('parent_id')
                    ->label('Επίπεδο')
                    ->placeholder('Όλες')
                    ->trueLabel('Υποκατηγορίες')
                    ->falseLabel('Κύριες Κατηγορίες')
                    ->nullable(),

                Tables\Filters\TrashedFilter::make()
                    ->label('Διαγραμμένες'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Προβολή'),
                Tables\Actions\EditAction::make()->label('Επεξεργασία'),
                Tables\Actions\DeleteAction::make()->label('Διαγραφή')
                    ->disabled(fn (Category $record) => $record->is_system),
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
            ])
            ->defaultSort('display_order', 'asc')
            ->reorderable('display_order')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Δημιουργία Κατηγορίας'),
            ])
            ->emptyStateHeading('Δεν υπάρχουν κατηγορίες')
            ->emptyStateDescription('Δημιουργήστε την πρώτη σας κατηγορία για να οργανώσετε τα δεδομένα σας.');
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
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

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_active', true)->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
