<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentInvoices extends BaseWidget
{
    protected static ?string $heading = 'Πρόσφατα Τιμολόγια';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Invoice::query()->latest()->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Αρ. Τιμολογίου')
                    ->searchable(),
                Tables\Columns\TextColumn::make('vendor.name')
                    ->label('Προμηθευτής')
                    ->searchable(),
                Tables\Columns\TextColumn::make('invoice_date')
                    ->label('Ημερομηνία')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Ποσό')
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Κατάσταση')
                    ->colors([
                        'gray' => 'draft',
                        'warning' => 'pending',
                        'success' => 'paid',
                        'danger' => 'overdue',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Πρόχειρο',
                        'pending' => 'Εκκρεμές',
                        'paid' => 'Πληρωμένο',
                        'overdue' => 'Ληξιπρόθεσμο',
                        'cancelled' => 'Ακυρωμένο',
                        default => $state,
                    }),
            ])
            ->paginated(false);
    }
}
