<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Store;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Vendor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationGroup = 'Οικονομικά';

    protected static ?string $navigationLabel = 'Συναλλαγές';

    protected static ?string $modelLabel = 'Συναλλαγή';

    protected static ?string $pluralModelLabel = 'Συναλλαγές';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    Forms\Components\Wizard\Step::make('Βασικά Στοιχεία')
                        ->description('Επιλέξτε τον τύπο και το κατάστημα')
                        ->icon('heroicon-o-building-storefront')
                        ->schema([
                            Forms\Components\Section::make()
                                ->schema([
                                    Forms\Components\Grid::make(2)
                                        ->schema([
                                            Forms\Components\ToggleButtons::make('type')
                                                ->label('Τύπος Συναλλαγής')
                                                ->options([
                                                    'income' => 'Έσοδο',
                                                    'expense' => 'Έξοδο',
                                                ])
                                                ->icons([
                                                    'income' => 'heroicon-o-arrow-trending-up',
                                                    'expense' => 'heroicon-o-arrow-trending-down',
                                                ])
                                                ->colors([
                                                    'income' => 'success',
                                                    'expense' => 'danger',
                                                ])
                                                ->inline()
                                                ->required()
                                                ->reactive()
                                                ->afterStateUpdated(function (Set $set, $state) {
                                                    // Reset related fields when type changes
                                                    $set('category_id', null);
                                                    $set('vendor_id', null);
                                                    $set('customer_id', null);
                                                })
                                                ->columnSpanFull(),

                                            Forms\Components\Select::make('store_id')
                                                ->label('Κατάστημα *')
                                                ->options(Store::pluck('name', 'id'))
                                                ->required()
                                                ->searchable()
                                                ->default(fn () => Store::first()?->id)
                                                ->reactive()
                                                ->helperText('Επιλέξτε το κατάστημα για τη συναλλαγή'),

                                            Forms\Components\Select::make('status')
                                                ->label('Κατάσταση *')
                                                ->options([
                                                    'draft' => 'Πρόχειρο',
                                                    'pending' => 'Σε Αναμονή',
                                                    'approved' => 'Εγκεκριμένο',
                                                    'posted' => 'Καταχωρημένο',
                                                    'cancelled' => 'Ακυρωμένο',
                                                ])
                                                ->required()
                                                ->default('draft')
                                                ->helperText('Η κατάσταση της συναλλαγής'),
                                        ]),
                                ]),
                        ]),

                    Forms\Components\Wizard\Step::make('Συμμετέχοντες')
                        ->description('Επιλέξτε προμηθευτή/πελάτη και κατηγορία')
                        ->icon('heroicon-o-user-group')
                        ->schema([
                            Forms\Components\Section::make()
                                ->schema([
                                    Forms\Components\Grid::make(2)
                                        ->schema([
                                            Forms\Components\Select::make('category_id')
                                                ->label('Κατηγορία')
                                                ->options(function (Get $get) {
                                                    $type = $get('type');
                                                    $storeId = $get('store_id');

                                                    if (! $type || ! $storeId) {
                                                        return [];
                                                    }

                                                    return Category::where('store_id', $storeId)
                                                        ->where('type', $type)
                                                        ->where('is_active', true)
                                                        ->orderBy('display_order')
                                                        ->pluck('name', 'id');
                                                })
                                                ->searchable()
                                                ->reactive()
                                                ->disabled(fn (Get $get) => ! $get('type') || ! $get('store_id'))
                                                ->helperText(fn (Get $get) => $get('type') === 'income'
                                                        ? 'Επιλέξτε κατηγορία εσόδων'
                                                        : ($get('type') === 'expense' ? 'Επιλέξτε κατηγορία εξόδων' : '')
                                                ),

                                            Forms\Components\Select::make('account_id')
                                                ->label('Λογαριασμός')
                                                ->options(function (Get $get) {
                                                    $storeId = $get('store_id');
                                                    if (! $storeId) {
                                                        return [];
                                                    }

                                                    return \App\Models\Account::where('store_id', $storeId)
                                                        ->where('is_active', true)
                                                        ->orderBy('is_primary', 'desc')
                                                        ->orderBy('name')
                                                        ->pluck('name', 'id');
                                                })
                                                ->searchable()
                                                ->required()
                                                ->reactive()
                                                ->disabled(fn (Get $get) => ! $get('store_id'))
                                                ->helperText('Επιλέξτε τον λογαριασμό για τη συναλλαγή'),
                                        ]),

                                    Forms\Components\Grid::make(2)
                                        ->schema([
                                            Forms\Components\Select::make('vendor_id')
                                                ->label('Προμηθευτής')
                                                ->options(function (Get $get) {
                                                    $storeId = $get('store_id');
                                                    if (! $storeId) {
                                                        return [];
                                                    }

                                                    return Vendor::where('store_id', $storeId)
                                                        ->where('is_active', true)
                                                        ->orderBy('name')
                                                        ->pluck('name', 'id');
                                                })
                                                ->searchable()
                                                ->visible(fn (Get $get) => $get('type') === 'expense')
                                                ->disabled(fn (Get $get) => ! $get('store_id'))
                                                ->helperText('Επιλέξτε τον προμηθευτή για το έξοδο'),

                                            Forms\Components\Select::make('customer_id')
                                                ->label('Πελάτης')
                                                ->options(function (Get $get) {
                                                    $storeId = $get('store_id');
                                                    if (! $storeId) {
                                                        return [];
                                                    }

                                                    return Customer::where('store_id', $storeId)
                                                        ->where('is_active', true)
                                                        ->orderBy('name')
                                                        ->pluck('name', 'id');
                                                })
                                                ->searchable()
                                                ->visible(fn (Get $get) => $get('type') === 'income')
                                                ->disabled(fn (Get $get) => ! $get('store_id'))
                                                ->helperText('Επιλέξτε τον πελάτη για το έσοδο'),
                                        ]),
                                ]),
                        ]),

                    Forms\Components\Wizard\Step::make('Οικονομικά Στοιχεία')
                        ->description('Συμπληρώστε τα ποσά και ημερομηνίες')
                        ->icon('heroicon-o-currency-euro')
                        ->schema([
                            Forms\Components\Section::make()
                                ->schema([
                                    Forms\Components\Grid::make(3)
                                        ->schema([
                                            Forms\Components\DatePicker::make('transaction_date')
                                                ->label('Ημερομηνία Συναλλαγής')
                                                ->required()
                                                ->default(now())
                                                ->native(false)
                                                ->helperText('Η ημερομηνία που έγινε η συναλλαγή'),

                                            Forms\Components\DatePicker::make('due_date')
                                                ->label('Ημερομηνία Λήξης')
                                                ->native(false)
                                                ->after('transaction_date')
                                                ->helperText('Προαιρετική ημερομηνία λήξης'),

                                            Forms\Components\TextInput::make('reference')
                                                ->label('Αριθμός Παραστατικού')
                                                ->maxLength(255)
                                                ->placeholder('π.χ. ΤΙΜ-001')
                                                ->helperText('Αριθμός τιμολογίου ή απόδειξης'),
                                        ]),

                                    Forms\Components\Grid::make(3)
                                        ->schema([
                                            Forms\Components\TextInput::make('subtotal')
                                                ->label('Υποσύνολο')
                                                ->numeric()
                                                ->prefix('€')
                                                ->required()
                                                ->reactive()
                                                ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                                    $taxTotal = $get('tax_total') ?? 0;
                                                    $total = ($state ?? 0) + $taxTotal;
                                                    $set('total', $total);
                                                })
                                                ->helperText('Ποσό χωρίς ΦΠΑ'),

                                            Forms\Components\TextInput::make('tax_total')
                                                ->label('ΦΠΑ')
                                                ->numeric()
                                                ->prefix('€')
                                                ->default(0)
                                                ->reactive()
                                                ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                                    $subtotal = $get('subtotal') ?? 0;
                                                    $total = $subtotal + ($state ?? 0);
                                                    $set('total', $total);
                                                })
                                                ->helperText('Ποσό ΦΠΑ'),

                                            Forms\Components\TextInput::make('total')
                                                ->label('Σύνολο')
                                                ->numeric()
                                                ->prefix('€')
                                                ->required()
                                                ->disabled()
                                                ->dehydrated()
                                                ->helperText('Τελικό ποσό με ΦΠΑ'),
                                        ]),

                                    Forms\Components\Textarea::make('memo')
                                        ->label('Σημειώσεις')
                                        ->rows(3)
                                        ->maxLength(1000)
                                        ->placeholder('Προαιρετικές σημειώσεις για τη συναλλαγή')
                                        ->columnSpanFull(),
                                ]),
                        ]),

                    Forms\Components\Wizard\Step::make('Έγκριση')
                        ->description('Στοιχεία έγκρισης και καταχώρησης')
                        ->icon('heroicon-o-check-badge')
                        ->schema([
                            Forms\Components\Section::make()
                                ->schema([
                                    Forms\Components\Grid::make(2)
                                        ->schema([
                                            Forms\Components\Select::make('user_id')
                                                ->label('Καταχωρητής')
                                                ->options(User::pluck('name', 'id'))
                                                ->default(auth()->id())
                                                ->disabled()
                                                ->dehydrated()
                                                ->helperText('Ο χρήστης που καταχωρεί τη συναλλαγή'),

                                            Forms\Components\Select::make('approved_by')
                                                ->label('Εγκρίθηκε από')
                                                ->options(User::pluck('name', 'id'))
                                                ->searchable()
                                                ->helperText('Ο χρήστης που ενέκρινε τη συναλλαγή'),
                                        ]),

                                    Forms\Components\Grid::make(2)
                                        ->schema([
                                            Forms\Components\DateTimePicker::make('approved_at')
                                                ->label('Ημ/νία Έγκρισης')
                                                ->native(false)
                                                ->seconds(false)
                                                ->helperText('Πότε εγκρίθηκε η συναλλαγή'),

                                            Forms\Components\DateTimePicker::make('posted_at')
                                                ->label('Ημ/νία Καταχώρησης')
                                                ->native(false)
                                                ->seconds(false)
                                                ->helperText('Πότε καταχωρήθηκε στα βιβλία'),
                                        ]),

                                    Forms\Components\TextInput::make('external_id')
                                        ->label('Εξωτερικός Κωδικός')
                                        ->maxLength(255)
                                        ->placeholder('π.χ. Κωδικός από άλλο σύστημα')
                                        ->helperText('Κωδικός αναφοράς από εξωτερικό σύστημα')
                                        ->columnSpanFull(),
                                ]),
                        ]),
                ])
                    ->columnSpanFull()
                    ->skippable()
                    ->persistStepInQueryString(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference')
                    ->label('Παραστατικό')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('type')
                    ->label('Τύπος')
                    ->badge()
                    ->colors([
                        'success' => 'income',
                        'danger' => 'expense',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'income' => 'Έσοδο',
                        'expense' => 'Έξοδο',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('store.name')
                    ->label('Κατάστημα')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Κατηγορία')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('account.name')
                    ->label('Λογαριασμός')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('vendor.name')
                    ->label('Προμηθευτής')
                    ->searchable()
                    ->sortable()
                    ->visible(fn () => true)
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Πελάτης')
                    ->searchable()
                    ->sortable()
                    ->visible(fn () => true)
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('transaction_date')
                    ->label('Ημερομηνία')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total')
                    ->label('Ποσό')
                    ->money('EUR')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money('EUR')
                            ->label('Σύνολο'),
                    ])
                    ->color(fn ($record) => $record->type === 'income' ? 'success' : 'danger'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Κατάσταση')
                    ->badge()
                    ->colors([
                        'gray' => 'draft',
                        'warning' => 'pending',
                        'info' => 'approved',
                        'success' => 'posted',
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Πρόχειρο',
                        'pending' => 'Σε Αναμονή',
                        'approved' => 'Εγκεκριμένο',
                        'posted' => 'Καταχωρημένο',
                        'cancelled' => 'Ακυρωμένο',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Καταχωρητής')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('approver.name')
                    ->label('Εγκρίθηκε από')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Δημιουργήθηκε')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Τύπος')
                    ->options([
                        'income' => 'Έσοδα',
                        'expense' => 'Έξοδα',
                    ]),

                Tables\Filters\SelectFilter::make('store_id')
                    ->label('Κατάστημα')
                    ->relationship('store', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Κατηγορία')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Κατάσταση')
                    ->options([
                        'draft' => 'Πρόχειρο',
                        'pending' => 'Σε Αναμονή',
                        'approved' => 'Εγκεκριμένο',
                        'posted' => 'Καταχωρημένο',
                        'cancelled' => 'Ακυρωμένο',
                    ]),

                Tables\Filters\SelectFilter::make('transaction_date_range')
                    ->label('Περίοδος')
                    ->options([
                        'today' => 'Σήμερα',
                        'yesterday' => 'Χθες',
                        'this_week' => 'Αυτή την Εβδομάδα',
                        'last_week' => 'Προηγούμενη Εβδομάδα',
                        'this_month' => 'Τρέχων Μήνας',
                        'last_month' => 'Προηγούμενος Μήνας',
                        'this_quarter' => 'Τρέχον Τρίμηνο',
                        'last_quarter' => 'Προηγούμενο Τρίμηνο',
                        'this_year' => 'Τρέχον Έτος',
                        'last_year' => 'Προηγούμενο Έτος',
                        'last_7_days' => 'Τελευταίες 7 Ημέρες',
                        'last_30_days' => 'Τελευταίες 30 Ημέρες',
                        'last_90_days' => 'Τελευταίες 90 Ημέρες',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (! $data['value']) {
                            return $query;
                        }

                        $now = now();

                        return match ($data['value']) {
                            'today' => $query->whereDate('transaction_date', $now->toDateString()),
                            'yesterday' => $query->whereDate('transaction_date', $now->yesterday()->toDateString()),
                            'this_week' => $query->whereBetween('transaction_date', [
                                $now->startOfWeek()->toDateString(),
                                $now->endOfWeek()->toDateString(),
                            ]),
                            'last_week' => $query->whereBetween('transaction_date', [
                                $now->subWeek()->startOfWeek()->toDateString(),
                                $now->subWeek()->endOfWeek()->toDateString(),
                            ]),
                            'this_month' => $query->whereMonth('transaction_date', $now->month)
                                ->whereYear('transaction_date', $now->year),
                            'last_month' => $query->whereMonth('transaction_date', $now->subMonth()->month)
                                ->whereYear('transaction_date', $now->subMonth()->year),
                            'this_quarter' => $query->whereBetween('transaction_date', [
                                $now->firstOfQuarter()->toDateString(),
                                $now->lastOfQuarter()->toDateString(),
                            ]),
                            'last_quarter' => $query->whereBetween('transaction_date', [
                                $now->subQuarter()->firstOfQuarter()->toDateString(),
                                $now->subQuarter()->lastOfQuarter()->toDateString(),
                            ]),
                            'this_year' => $query->whereYear('transaction_date', $now->year),
                            'last_year' => $query->whereYear('transaction_date', $now->subYear()->year),
                            'last_7_days' => $query->whereBetween('transaction_date', [
                                $now->subDays(6)->toDateString(),
                                $now->toDateString(),
                            ]),
                            'last_30_days' => $query->whereBetween('transaction_date', [
                                $now->subDays(29)->toDateString(),
                                $now->toDateString(),
                            ]),
                            'last_90_days' => $query->whereBetween('transaction_date', [
                                $now->subDays(89)->toDateString(),
                                $now->toDateString(),
                            ]),
                            default => $query,
                        };
                    })
                    ->default('this_month'),

                Tables\Filters\Filter::make('custom_date_range')
                    ->label('Προσαρμοσμένη Περίοδος')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Από')
                            ->native(false),
                        Forms\Components\DatePicker::make('until')
                            ->label('Έως')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('transaction_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('transaction_date', '<=', $date),
                            );
                    })
                    ->columnSpan(2),

                Tables\Filters\SelectFilter::make('vendor_id')
                    ->label('Προμηθευτής')
                    ->relationship('vendor', 'name')
                    ->searchable()
                    ->preload()
                    ->visible(fn () => true),

                Tables\Filters\SelectFilter::make('customer_id')
                    ->label('Πελάτης')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload()
                    ->visible(fn () => true),

                Tables\Filters\TrashedFilter::make()
                    ->label('Διαγραμμένες'),
            ])
            ->filtersLayout(Tables\Enums\FiltersLayout::AboveContentCollapsible)
            ->persistFiltersInSession()
            ->actions([
                Tables\Actions\ViewAction::make()->label('Προβολή'),
                Tables\Actions\EditAction::make()->label('Επεξεργασία'),
                Tables\Actions\DeleteAction::make()->label('Διαγραφή'),
                Tables\Actions\Action::make('approve')
                    ->label('Έγκριση')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Transaction $record) => $record->status === 'pending')
                    ->action(function (Transaction $record) {
                        $record->update([
                            'status' => 'approved',
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                        ]);
                    }),
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
            ->headerActions([
                Tables\Actions\Action::make('export')
                    ->label('Εξαγωγή σε Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function ($livewire) {
                        $filename = 'transactions_'.now()->format('Y-m-d_His').'.csv';

                        return response()->streamDownload(function () use ($livewire) {
                            $transactions = $livewire->getFilteredTableQuery()->get();

                            echo "\xEF\xBB\xBF"; // UTF-8 BOM
                            echo "Παραστατικό,Τύπος,Κατάστημα,Κατηγορία,Προμηθευτής,Πελάτης,Ημερομηνία,Υποσύνολο,ΦΠΑ,Σύνολο,Κατάσταση\n";

                            foreach ($transactions as $transaction) {
                                echo implode(',', [
                                    $transaction->reference ?? '',
                                    $transaction->type === 'income' ? 'Έσοδο' : 'Έξοδο',
                                    $transaction->store?->name ?? '',
                                    $transaction->category?->name ?? '',
                                    $transaction->vendor?->name ?? '-',
                                    $transaction->customer?->name ?? '-',
                                    $transaction->transaction_date?->format('d/m/Y') ?? '',
                                    number_format($transaction->subtotal ?? 0, 2, '.', ''),
                                    number_format($transaction->tax_total ?? 0, 2, '.', ''),
                                    number_format($transaction->total ?? 0, 2, '.', ''),
                                    match ($transaction->status) {
                                        'draft' => 'Πρόχειρο',
                                        'pending' => 'Σε Αναμονή',
                                        'approved' => 'Εγκεκριμένο',
                                        'posted' => 'Καταχωρημένο',
                                        'cancelled' => 'Ακυρωμένο',
                                        default => $transaction->status
                                    },
                                ])."\n";
                            }
                        }, $filename);
                    }),

                Tables\Actions\Action::make('quick_stats')
                    ->label('Γρήγορα Στατιστικά')
                    ->icon('heroicon-o-chart-pie')
                    ->color('info')
                    ->modalHeading('Στατιστικά Περιόδου')
                    ->modalContent(function ($livewire) {
                        $query = $livewire->getFilteredTableQuery();

                        $totalIncome = (clone $query)->where('type', 'income')->sum('total');
                        $totalExpense = (clone $query)->where('type', 'expense')->sum('total');
                        $balance = $totalIncome - $totalExpense;
                        $count = $query->count();

                        return view('filament.resources.transaction-stats', [
                            'totalIncome' => $totalIncome,
                            'totalExpense' => $totalExpense,
                            'balance' => $balance,
                            'count' => $count,
                        ]);
                    })
                    ->modalButton('Κλείσιμο'),
            ])
            ->defaultSort('transaction_date', 'desc')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Δημιουργία Συναλλαγής'),
            ])
            ->emptyStateHeading('Δεν υπάρχουν συναλλαγές')
            ->emptyStateDescription('Δημιουργήστε την πρώτη σας συναλλαγή για να ξεκινήσετε την καταγραφή εσόδων και εξόδων.');
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
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'view' => Pages\ViewTransaction::route('/{record}'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with(['store', 'category', 'account', 'vendor', 'customer', 'creator', 'approver'])
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
        return static::getModel()::where('status', 'pending')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getWidgets(): array
    {
        return [
            TransactionResource\Widgets\TransactionStats::class,
        ];
    }
}
