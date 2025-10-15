<?php

namespace App\Filament\Widgets;

use App\Models\Store;
use App\Support\StoreContext;
use Filament\Widgets\Widget;

class StoreSelectorWidget extends Widget
{
    protected static string $view = 'filament.widgets.store-selector-widget';

    protected static ?int $sort = -10;

    protected int|string|array $columnSpan = 'full';

    protected $listeners = ['store-changed' => '$refresh'];

    public ?int $selectedStoreId = null;

    public function mount(): void
    {
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

        // Refresh all other widgets
        $this->dispatch('store-changed');

        // Reload the page to ensure all widgets use the new context
        $this->js('window.location.reload()');
    }

    public function getStores()
    {
        return auth()->user()->stores;
    }
}
