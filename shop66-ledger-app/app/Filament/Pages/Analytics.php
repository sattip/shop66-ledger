<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Analytics extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'Στατιστικά & Αναλύσεις';

    protected static ?string $navigationLabel = 'Αναλυτικά Στοιχεία';

    protected static ?string $title = 'Αναλυτικά Στοιχεία';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.analytics';

    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\SalesAnalytics::class,
            \App\Filament\Widgets\TopVendorsChart::class,
            \App\Filament\Widgets\CategoryBreakdown::class,
            \App\Filament\Widgets\MonthlyComparison::class,
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\AnalyticsStatsOverview::class,
        ];
    }

    public function getColumns(): int|string|array
    {
        return [
            'sm' => 1,
            'md' => 2,
            'lg' => 2,
            'xl' => 3,
        ];
    }
}
