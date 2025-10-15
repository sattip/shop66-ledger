<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Επιλέξτε Κατάστημα
        </x-slot>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach($this->getStores() as $store)
                <button
                    wire:click="selectStore({{ $store->id }})"
                    type="button"
                    class="text-left p-4 rounded-lg transition-all duration-200
                        {{ $selectedStoreId === $store->id
                            ? 'bg-primary-500 text-white ring-2 ring-primary-600'
                            : 'bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700' }}"
                >
                    <h3 class="font-semibold text-base">{{ $store->name }}</h3>
                    <p class="text-sm {{ $selectedStoreId === $store->id ? 'text-primary-100' : 'text-gray-500 dark:text-gray-400' }}">
                        {{ $store->city }}
                    </p>
                </button>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
