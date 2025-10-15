<?php

namespace App\Filament\Pages;

use App\Models\Store;
use App\Support\StoreContext;
use Filament\Pages\Page;

class StoresManagement extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static string $view = 'filament.pages.stores-management';

    protected static ?string $title = 'Γρήγορη Διαχείριση';

    protected static ?string $navigationLabel = 'Γρήγορη Διαχείριση';

    protected static ?int $navigationSort = -2;

    protected static ?string $navigationGroup = null;

    public ?int $selectedStoreId = null;

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\StoreSelectorWidget::class,
            \App\Filament\Widgets\StoreQuickStatsWidget::class,
            \App\Filament\Widgets\QuickAddTransactionWidget::class,
            \App\Filament\Widgets\FilteredTransactionsWidget::class,
        ];
    }

    public function mount(): void
    {
        // Get current store context or user's first store
        $this->selectedStoreId = app(StoreContext::class)->get()
            ?? auth()->user()->stores()->first()?->id;

        if ($this->selectedStoreId) {
            app(StoreContext::class)->set($this->selectedStoreId);
            app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($this->selectedStoreId);
        }
    }

    public function selectStore(int $storeId): void
    {
        $this->selectedStoreId = $storeId;

        // Update store context
        app(StoreContext::class)->set($storeId);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($storeId);

        // Refresh widgets
        $this->dispatch('store-changed');
    }

    public function getStores()
    {
        return auth()->user()->stores;
    }

    public function getSelectedStore()
    {
        if (! $this->selectedStoreId) {
            return null;
        }

        return Store::find($this->selectedStoreId);
    }

    public static function canAccess(): bool
    {
        return auth()->user()->stores()->exists();
    }
}
