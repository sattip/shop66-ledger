<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-heroicon-o-plus-circle class="h-5 w-5 text-primary-500" />
                <span>Γρήγορη Καταχώρηση Συναλλαγής</span>
            </div>
        </x-slot>

        <form wire:submit="addTransaction">
            {{ $this->form }}

            <div class="mt-4 flex justify-end">
                <x-filament::button type="submit" icon="heroicon-o-check" color="success">
                    Καταχώρηση
                </x-filament::button>
            </div>
        </form>
    </x-filament::section>
</x-filament-widgets::widget>
