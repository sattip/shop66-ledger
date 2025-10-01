<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Dashboard';

    protected static ?string $navigationLabel = 'Αρχική';

    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?int $navigationSort = -1;

    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\StoreStatsOverview::class,
            \App\Filament\Widgets\RecentInvoices::class,
            \App\Filament\Widgets\MonthlyRevenueChart::class,
        ];
    }

    public function getColumns(): int|string|array
    {
        return [
            'md' => 2,
            'xl' => 3,
        ];
    }
}
