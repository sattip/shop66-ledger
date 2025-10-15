<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class FilteredTransactionsWidget extends BaseWidget
{
    protected static ?int $sort = 10;

    protected int|string|array $columnSpan = 'full';

    protected $listeners = ['store-changed' => '$refresh', 'transaction-added' => '$refresh'];

    public ?array $filters = [];

    public function mount(): void
    {
        $this->filters = [
            'date_from' => Carbon::now()->startOfMonth()->format('Y-m-d'),
            'date_to' => Carbon::now()->format('Y-m-d'),
            'type' => 'all',
            'category_id' => 'all',
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getFilteredQuery())
            ->columns([
                Tables\Columns\TextColumn::make('transaction_date')
                    ->label('Ημερομηνία')
                    ->date('d/m/Y')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Κατηγορία')
                    ->badge()
                    ->color(fn (Transaction $record) => $record->type === 'income' ? 'success' : 'danger')
                    ->icon(fn (Transaction $record) => $record->type === 'income' ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Τύπος')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'income' ? 'Έσοδο' : 'Έξοδο')
                    ->color(fn (string $state): string => $state === 'income' ? 'success' : 'danger')
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Περιγραφή')
                    ->limit(50)
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('total')
                    ->label('Ποσό')
                    ->money('EUR')
                    ->sortable()
                    ->alignEnd()
                    ->color(fn (Transaction $record) => $record->type === 'income' ? 'success' : 'danger')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Κατάσταση')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Πρόχειρο',
                        'pending' => 'Σε Αναμονή',
                        'approved' => 'Εγκεκριμένο',
                        'posted' => 'Καταχωρημένο',
                        'cancelled' => 'Ακυρωμένο',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'pending' => 'warning',
                        'approved' => 'info',
                        'posted' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->toggleable(),
            ])
            ->defaultSort('transaction_date', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }

    protected function getFilteredQuery()
    {
        $storeId = app(\App\Support\StoreContext::class)->get();

        if (! $storeId) {
            return Transaction::query()->whereRaw('1 = 0');
        }

        $query = Transaction::query()
            ->where('store_id', $storeId)
            ->with(['category']);

        // Date filters
        if (! empty($this->filters['date_from'])) {
            $query->where('transaction_date', '>=', $this->filters['date_from']);
        }

        if (! empty($this->filters['date_to'])) {
            $query->where('transaction_date', '<=', $this->filters['date_to']);
        }

        // Type filter
        if (! empty($this->filters['type']) && $this->filters['type'] !== 'all') {
            $query->where('type', $this->filters['type']);
        }

        // Category filter
        if (! empty($this->filters['category_id']) && $this->filters['category_id'] !== 'all') {
            $query->where('category_id', $this->filters['category_id']);
        }

        return $query;
    }

    public function getTableHeader(): ?\Illuminate\Contracts\View\View
    {
        return view('filament.widgets.filtered-transactions-header', [
            'widget' => $this,
        ]);
    }

    public function updateFilters(): void
    {
        $this->dispatch('$refresh');
    }

    public function resetFilters(): void
    {
        $this->filters = [
            'date_from' => Carbon::now()->startOfMonth()->format('Y-m-d'),
            'date_to' => Carbon::now()->format('Y-m-d'),
            'type' => 'all',
            'category_id' => 'all',
        ];

        $this->dispatch('$refresh');
    }

    public function getSummary(): array
    {
        $storeId = app(\App\Support\StoreContext::class)->get();

        if (! $storeId) {
            return [
                'total_income' => 0,
                'total_expense' => 0,
                'net' => 0,
            ];
        }

        $query = $this->getFilteredQuery();

        $income = (clone $query)->where('type', 'income')->where('status', 'posted')->sum('total');
        $expense = (clone $query)->where('type', 'expense')->where('status', 'posted')->sum('total');

        return [
            'total_income' => $income,
            'total_expense' => $expense,
            'net' => $income - $expense,
        ];
    }
}
