<x-filament-panels::page>
    <div class="grid gap-6">
        @if($this->getHeaderWidgets())
            <div>
                <x-filament-widgets::widgets
                    :widgets="$this->getHeaderWidgets()"
                    :columns="$this->getColumns()"
                />
            </div>
        @endif

        <div>
            <x-filament-widgets::widgets
                :widgets="$this->getWidgets()"
                :columns="$this->getColumns()"
            />
        </div>
    </div>
</x-filament-panels::page>