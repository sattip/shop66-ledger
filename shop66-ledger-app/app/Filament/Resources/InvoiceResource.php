<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Filament\Resources\InvoiceResource\RelationManagers;
use App\Models\Invoice;
use App\Models\Store;
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
use Illuminate\Support\HtmlString;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Οικονομικά';

    protected static ?string $navigationLabel = 'Τιμολόγια';

    protected static ?string $modelLabel = 'Τιμολόγιο';

    protected static ?string $pluralModelLabel = 'Τιμολόγια';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Στοιχεία Τιμολογίου')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Select::make('store_id')
                                    ->label('Κατάστημα')
                                    ->options(function () {
                                        $user = auth()->user();
                                        if (! $user) {
                                            return [];
                                        }

                                        return $user->stores()->pluck('name', 'id');
                                    })
                                    ->required()
                                    ->searchable()
                                    ->default(fn () => auth()->user()?->stores()->first()?->id)
                                    ->reactive()
                                    ->helperText('Επιλέξτε κατάστημα από τα καταστήματά σας'),
                                Forms\Components\Select::make('vendor_id')
                                    ->label('Προμηθευτής')
                                    ->options(fn (Get $get) => Vendor::where('store_id', $get('store_id'))->pluck('name', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->reactive()
                                    ->disabled(fn (Get $get) => ! $get('store_id')),
                                Forms\Components\TextInput::make('invoice_number')
                                    ->label('Αριθμός Τιμολογίου')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->placeholder('Θα δημιουργηθεί αυτόματα αν δεν συμπληρωθεί'),
                            ]),
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\DatePicker::make('invoice_date')
                                    ->label('Ημερομηνία Τιμολογίου')
                                    ->required()
                                    ->default(now())
                                    ->native(false),
                                Forms\Components\DatePicker::make('due_date')
                                    ->label('Ημερομηνία Λήξης')
                                    ->native(false)
                                    ->after('invoice_date'),
                                Forms\Components\Select::make('status')
                                    ->label('Κατάσταση')
                                    ->options([
                                        'draft' => 'Πρόχειρο',
                                        'pending' => 'Εκκρεμεί',
                                        'paid' => 'Πληρωμένο',
                                        'overdue' => 'Ληξιπρόθεσμο',
                                        'cancelled' => 'Ακυρωμένο',
                                    ])
                                    ->required()
                                    ->default('pending'),
                            ]),
                    ]),

                Forms\Components\Section::make('Τύπος Τιμολογίου')
                    ->schema([
                        Forms\Components\Radio::make('invoice_type')
                            ->label('Τύπος Τιμολογίου')
                            ->options([
                                'simple' => 'Απλό Τιμολόγιο (Μόνο Σύνολο)',
                                'detailed' => 'Αναλυτικό Τιμολόγιο (Με Είδη)',
                            ])
                            ->required()
                            ->default('simple')
                            ->reactive()
                            ->helperText('Επιλέξτε απλό για γρήγορη καταχώρηση με μόνο το σύνολο, ή αναλυτικό για τιμολόγια με είδη'),
                    ]),

                Forms\Components\Section::make('Στοιχεία Απλού Τιμολογίου')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('total_amount')
                                    ->label('Συνολικό Ποσό')
                                    ->numeric()
                                    ->prefix('€')
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                        if ($get('invoice_type') === 'simple') {
                                            $taxAmount = $get('tax_amount') ?? 0;
                                            $discountAmount = $get('discount_amount') ?? 0;
                                            $subtotal = $state - $taxAmount + $discountAmount;
                                            $set('subtotal', $subtotal);
                                        }
                                    }),
                                Forms\Components\TextInput::make('tax_amount')
                                    ->label('Ποσό ΦΠΑ')
                                    ->numeric()
                                    ->prefix('€')
                                    ->default(0)
                                    ->reactive()
                                    ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                        if ($get('invoice_type') === 'simple') {
                                            $totalAmount = $get('total_amount') ?? 0;
                                            $discountAmount = $get('discount_amount') ?? 0;
                                            $subtotal = $totalAmount - $state + $discountAmount;
                                            $set('subtotal', $subtotal);
                                        }
                                    }),
                            ]),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('discount_amount')
                                    ->label('Ποσό Έκπτωσης')
                                    ->numeric()
                                    ->prefix('€')
                                    ->default(0)
                                    ->reactive()
                                    ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                        if ($get('invoice_type') === 'simple') {
                                            $totalAmount = $get('total_amount') ?? 0;
                                            $taxAmount = $get('tax_amount') ?? 0;
                                            $subtotal = $totalAmount - $taxAmount + $state;
                                            $set('subtotal', $subtotal);
                                        }
                                    }),
                                Forms\Components\TextInput::make('subtotal')
                                    ->label('Υποσύνολο (Υπολογισμένο)')
                                    ->numeric()
                                    ->prefix('€')
                                    ->disabled()
                                    ->dehydrated(),
                            ]),
                    ])
                    ->visible(fn (Get $get) => $get('invoice_type') === 'simple'),

                Forms\Components\Section::make('Είδη Τιμολογίου')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Forms\Components\Grid::make(12)
                                    ->schema([
                                        Forms\Components\Select::make('item_id')
                                            ->label('Είδος')
                                            ->relationship('item', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->createOptionForm([
                                                Forms\Components\Hidden::make('store_id')
                                                    ->default(fn () => \App\Models\Store::first()?->id),
                                                Forms\Components\TextInput::make('name')
                                                    ->label('Όνομα Είδους')
                                                    ->required()
                                                    ->maxLength(255),
                                                Forms\Components\TextInput::make('sku')
                                                    ->label('Κωδικός')
                                                    ->required()
                                                    ->unique('items', 'sku', ignoreRecord: true)
                                                    ->maxLength(100),
                                                Forms\Components\TextInput::make('price')
                                                    ->label('Τιμή')
                                                    ->numeric()
                                                    ->prefix('€')
                                                    ->required(),
                                                Forms\Components\Select::make('category_id')
                                                    ->label('Κατηγορία')
                                                    ->relationship('category', 'name')
                                                    ->searchable()
                                                    ->required(),
                                                Forms\Components\Hidden::make('slug')
                                                    ->default(fn ($get) => \Illuminate\Support\Str::slug($get('name') ?? '')),
                                            ])
                                            ->createOptionAction(function (Forms\Components\Actions\Action $action) {
                                                return $action
                                                    ->modalHeading('Δημιουργία Νέου Είδους')
                                                    ->modalButton('Δημιουργία')
                                                    ->modalWidth('lg');
                                            })
                                            ->required()
                                            ->columnSpan(4)
                                            ->reactive()
                                            ->afterStateUpdated(function (Set $set, $state) {
                                                if ($state) {
                                                    $item = \App\Models\Item::find($state);
                                                    if ($item) {
                                                        $set('unit_price', $item->price ?? 0);
                                                    }
                                                }
                                            }),
                                        Forms\Components\TextInput::make('quantity')
                                            ->label('Ποσότητα')
                                            ->numeric()
                                            ->default(1)
                                            ->required()
                                            ->columnSpan(2)
                                            ->reactive(),
                                        Forms\Components\TextInput::make('unit_price')
                                            ->label('Τιμή Μονάδας')
                                            ->numeric()
                                            ->prefix('€')
                                            ->required()
                                            ->columnSpan(2)
                                            ->reactive(),
                                        Forms\Components\TextInput::make('total')
                                            ->label('Σύνολο')
                                            ->numeric()
                                            ->prefix('€')
                                            ->disabled()
                                            ->columnSpan(2)
                                            ->dehydrated(false)
                                            ->reactive(),
                                    ]),
                                Forms\Components\Grid::make(12)
                                    ->schema([
                                        Forms\Components\TextInput::make('discount_percent')
                                            ->label('Έκπτωση %')
                                            ->numeric()
                                            ->suffix('%')
                                            ->default(0)
                                            ->columnSpan(3)
                                            ->reactive(),
                                        Forms\Components\TextInput::make('discount_amount')
                                            ->label('Ποσό Έκπτ.')
                                            ->numeric()
                                            ->prefix('€')
                                            ->default(0)
                                            ->columnSpan(3)
                                            ->reactive(),
                                        Forms\Components\TextInput::make('tax_rate')
                                            ->label('ΦΠΑ %')
                                            ->numeric()
                                            ->suffix('%')
                                            ->default(0)
                                            ->columnSpan(3)
                                            ->reactive(),
                                        Forms\Components\TextInput::make('tax_amount')
                                            ->label('Ποσό ΦΠΑ')
                                            ->numeric()
                                            ->prefix('€')
                                            ->default(0)
                                            ->columnSpan(3)
                                            ->reactive(),
                                    ]),
                            ])
                            ->defaultItems(1)
                            ->reorderable()
                            ->collapsible()
                            ->cloneable()
                            ->itemLabel(function (array $state): ?string {
                                if (isset($state['item_id'])) {
                                    $item = \App\Models\Item::find($state['item_id']);

                                    return $item ? $item->name : null;
                                }

                                return null;
                            }),
                        Forms\Components\Placeholder::make('totals')
                            ->content(function (Get $get) {
                                $items = $get('items') ?? [];
                                $subtotal = 0;
                                $totalTax = 0;
                                $totalDiscount = 0;

                                foreach ($items as $item) {
                                    $quantity = $item['quantity'] ?? 0;
                                    $unitPrice = $item['unit_price'] ?? 0;
                                    $discountAmount = $item['discount_amount'] ?? 0;
                                    $taxAmount = $item['tax_amount'] ?? 0;

                                    $lineTotal = ($quantity * $unitPrice) - $discountAmount + $taxAmount;
                                    $subtotal += ($quantity * $unitPrice);
                                    $totalDiscount += $discountAmount;
                                    $totalTax += $taxAmount;
                                }

                                $total = $subtotal - $totalDiscount + $totalTax;

                                return new HtmlString("
                                    <div class='text-right space-y-1'>
                                        <div>Υποσύνολο: <strong>€".number_format($subtotal, 2).'</strong></div>
                                        <div>Έκπτωση: <strong>-€'.number_format($totalDiscount, 2).'</strong></div>
                                        <div>ΦΠΑ: <strong>€'.number_format($totalTax, 2)."</strong></div>
                                        <div class='text-lg'>Σύνολο: <strong>€".number_format($total, 2).'</strong></div>
                                    </div>
                                ');
                            }),
                    ])
                    ->visible(fn (Get $get) => $get('invoice_type') === 'detailed'),

                Forms\Components\Section::make('Πρόσθετες Πληροφορίες')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Σημειώσεις')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Αρ. Τιμολογίου')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('vendor.name')
                    ->label('Προμηθευτής')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('invoice_date')
                    ->label('Ημερομηνία Τιμολογίου')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Ημερομηνία Λήξης')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('invoice_type')
                    ->label('Τύπος')
                    ->colors([
                        'primary' => 'simple',
                        'success' => 'detailed',
                    ]),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Συνολικό Ποσό')
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Κατάσταση')
                    ->colors([
                        'gray' => 'draft',
                        'warning' => 'pending',
                        'success' => 'paid',
                        'danger' => 'overdue',
                        'secondary' => 'cancelled',
                    ]),
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

                Tables\Filters\SelectFilter::make('vendor_id')
                    ->label('Προμηθευτής')
                    ->relationship('vendor', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('invoice_type')
                    ->label('Τύπος Τιμολογίου')
                    ->options([
                        'simple' => 'Απλό',
                        'detailed' => 'Αναλυτικό',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Κατάσταση')
                    ->options([
                        'draft' => 'Πρόχειρο',
                        'pending' => 'Εκκρεμεί',
                        'paid' => 'Πληρωμένο',
                        'overdue' => 'Ληξιπρόθεσμο',
                        'cancelled' => 'Ακυρωμένο',
                    ]),

                Tables\Filters\SelectFilter::make('invoice_date_range')
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
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (! $data['value']) {
                            return $query;
                        }

                        $now = now();

                        return match ($data['value']) {
                            'today' => $query->whereDate('invoice_date', $now->toDateString()),
                            'yesterday' => $query->whereDate('invoice_date', $now->yesterday()->toDateString()),
                            'this_week' => $query->whereBetween('invoice_date', [
                                $now->startOfWeek()->toDateString(),
                                $now->endOfWeek()->toDateString(),
                            ]),
                            'last_week' => $query->whereBetween('invoice_date', [
                                $now->subWeek()->startOfWeek()->toDateString(),
                                $now->subWeek()->endOfWeek()->toDateString(),
                            ]),
                            'this_month' => $query->whereMonth('invoice_date', $now->month)
                                ->whereYear('invoice_date', $now->year),
                            'last_month' => $query->whereMonth('invoice_date', $now->subMonth()->month)
                                ->whereYear('invoice_date', $now->subMonth()->year),
                            'this_quarter' => $query->whereBetween('invoice_date', [
                                $now->firstOfQuarter()->toDateString(),
                                $now->lastOfQuarter()->toDateString(),
                            ]),
                            'last_quarter' => $query->whereBetween('invoice_date', [
                                $now->subQuarter()->firstOfQuarter()->toDateString(),
                                $now->subQuarter()->lastOfQuarter()->toDateString(),
                            ]),
                            'this_year' => $query->whereYear('invoice_date', $now->year),
                            'last_year' => $query->whereYear('invoice_date', $now->subYear()->year),
                            default => $query,
                        };
                    }),

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
                                fn (Builder $query, $date): Builder => $query->whereDate('invoice_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('invoice_date', '<=', $date),
                            );
                    }),

                Tables\Filters\TrashedFilter::make()
                    ->label('Διαγραμμένα'),
            ])
            ->filtersLayout(Tables\Enums\FiltersLayout::AboveContentCollapsible)
            ->persistFiltersInSession()
            ->actions([
                Tables\Actions\ViewAction::make()->label('Προβολή'),
                Tables\Actions\EditAction::make()->label('Επεξεργασία'),
                Tables\Actions\DeleteAction::make()->label('Διαγραφή'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('export')
                    ->label('Εξαγωγή σε Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function ($livewire) {
                        $filename = 'invoices_'.now()->format('Y-m-d_His').'.csv';

                        return response()->streamDownload(function () use ($livewire) {
                            $invoices = $livewire->getFilteredTableQuery()->get();

                            echo "\xEF\xBB\xBF"; // UTF-8 BOM
                            echo "Αρ. Τιμολογίου,Προμηθευτής,Ημερομηνία,Ημ. Λήξης,Τύπος,Υποσύνολο,ΦΠΑ,Σύνολο,Κατάσταση\n";

                            foreach ($invoices as $invoice) {
                                echo implode(',', [
                                    $invoice->invoice_number,
                                    $invoice->vendor?->name ?? '',
                                    $invoice->invoice_date?->format('d/m/Y') ?? '',
                                    $invoice->due_date?->format('d/m/Y') ?? '',
                                    $invoice->invoice_type === 'simple' ? 'Απλό' : 'Αναλυτικό',
                                    number_format($invoice->subtotal ?? 0, 2, '.', ''),
                                    number_format($invoice->tax_amount ?? 0, 2, '.', ''),
                                    number_format($invoice->total_amount ?? 0, 2, '.', ''),
                                    match ($invoice->status) {
                                        'draft' => 'Πρόχειρο',
                                        'pending' => 'Εκκρεμεί',
                                        'paid' => 'Πληρωμένο',
                                        'overdue' => 'Ληξιπρόθεσμο',
                                        'cancelled' => 'Ακυρωμένο',
                                        default => $invoice->status
                                    },
                                ])."\n";
                            }
                        }, $filename);
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'view' => Pages\ViewInvoice::route('/{record}'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with(['store', 'vendor', 'items'])
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
