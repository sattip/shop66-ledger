<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'MoneyBoard';

    protected static ?string $navigationLabel = 'Επισκόπηση';

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?int $navigationSort = -1;

    protected static ?string $navigationGroup = 'Επισκόπηση';

    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\FinancialOverviewWidget::class,
            \App\Filament\Widgets\CashFlowChartWidget::class,
            \App\Filament\Widgets\ExpenseByCategoryWidget::class,
        ];
    }

    public function getColumns(): int|string|array
    {
        return [
            'default' => 1,
            'md' => 2,
            'xl' => 4,
        ];
    }
}
