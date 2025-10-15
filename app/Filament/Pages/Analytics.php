<?php

namespace App\Filament\Pages;

use App\Models\Store;
use App\Support\StoreContext;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;

class Analytics extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $title = 'Αναλυτικά Στοιχεία';

    protected static ?string $navigationLabel = 'Αναλυτικά';

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationGroup = null;

    protected static string $view = 'filament.pages.analytics';

    public ?array $data = [];

    public function getWidgets(): array
    {
        // Return empty to prevent panel-level widgets from showing
        return [];
    }

    public function getColumns(): int|string|array
    {
        return [
            'default' => 1,
            'md' => 2,
            'xl' => 4,
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\AnalyticsFiltersWidget::class,
            \App\Filament\Widgets\PerformanceInsightsWidget::class,
            \App\Filament\Widgets\CategoryAnalysisWidget::class,
            \App\Filament\Widgets\StoreAnalysisWidget::class,
            \App\Filament\Widgets\EfficiencyRatiosWidget::class,
        ];
    }

    public function mount(): void
    {
        // Set default store to current store context or user's first store
        $defaultStore = app(StoreContext::class)->get()
            ?? auth()->user()->stores()->first()?->id;

        // Update store context when component mounts
        if ($defaultStore) {
            app(StoreContext::class)->set($defaultStore);
            app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($defaultStore);
        }

        // Initialize form data
        $this->form->fill([
            'selectedStore' => $defaultStore,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('selectedStore')
                    ->label('')
                    ->placeholder('Επιλέξτε Κατάστημα')
                    ->options(
                        auth()->user()
                            ->stores()
                            ->pluck('stores.name', 'stores.id')
                            ->toArray()
                    )
                    ->live()
                    ->searchable()
                    ->native(false)
                    ->prefixIcon('heroicon-o-building-storefront')
                    ->afterStateUpdated(function ($state) {
                        if (! $state) {
                            return;
                        }

                        // Update store context
                        app(StoreContext::class)->set($state);
                        // Also set permission team ID for the new store
                        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($state);

                        // Force refresh of all widgets
                        $this->dispatch('store-changed');
                    }),
            ])
            ->statePath('data');
    }

    public function getHeader(): ?\Illuminate\Contracts\View\View
    {
        return view('filament.pages.analytics-header', [
            'form' => $this->form,
        ]);
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        // Get user's first store for permission check
        $store = $user->stores()->first();

        if (! $store) {
            return false;
        }

        // Set store context for permission check
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($store->id);

        return $user->can('view-analytics');
    }
}
