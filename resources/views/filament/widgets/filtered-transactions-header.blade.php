<div class="fi-wi-filtered-transactions-header mb-4">
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center justify-between w-full">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-funnel class="h-5 w-5 text-primary-500" />
                    <span>Φίλτρα & Αναφορές</span>
                </div>

                @php
                    $summary = $widget->getSummary();
                @endphp

                <div class="flex items-center gap-6 text-sm">
                    <div class="flex items-center gap-2">
                        <span class="text-gray-500 dark:text-gray-400">Έσοδα:</span>
                        <span class="font-semibold text-success-600 dark:text-success-400">
                            €{{ number_format($summary['total_income'], 2) }}
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-gray-500 dark:text-gray-400">Έξοδα:</span>
                        <span class="font-semibold text-danger-600 dark:text-danger-400">
                            €{{ number_format($summary['total_expense'], 2) }}
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-gray-500 dark:text-gray-400">Καθαρό:</span>
                        <span class="font-semibold {{ $summary['net'] >= 0 ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }}">
                            €{{ number_format($summary['net'], 2) }}
                        </span>
                    </div>
                </div>
            </div>
        </x-slot>

        <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <div class="md:col-span-1">
                <x-filament::input.wrapper>
                    <x-filament::input
                        type="date"
                        wire:model.live="filters.date_from"
                        placeholder="Από"
                    />
                </x-filament::input.wrapper>
                <x-filament::field-wrapper.label class="text-xs text-gray-500 mt-1">
                    Από
                </x-filament::field-wrapper.label>
            </div>

            <div class="md:col-span-1">
                <x-filament::input.wrapper>
                    <x-filament::input
                        type="date"
                        wire:model.live="filters.date_to"
                        placeholder="Έως"
                    />
                </x-filament::input.wrapper>
                <x-filament::field-wrapper.label class="text-xs text-gray-500 mt-1">
                    Έως
                </x-filament::field-wrapper.label>
            </div>

            <div class="md:col-span-1">
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model.live="filters.type">
                        <option value="all">Όλα</option>
                        <option value="income">Έσοδα</option>
                        <option value="expense">Έξοδα</option>
                    </x-filament::input.select>
                </x-filament::input.wrapper>
                <x-filament::field-wrapper.label class="text-xs text-gray-500 mt-1">
                    Τύπος
                </x-filament::field-wrapper.label>
            </div>

            <div class="md:col-span-2">
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model.live="filters.category_id">
                        <option value="all">Όλες οι Κατηγορίες</option>
                        @foreach(\App\Models\Category::all() as $category)
                            <option value="{{ $category->id }}">{{ $category->name }} ({{ $category->type === 'income' ? 'Έσοδο' : 'Έξοδο' }})</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
                <x-filament::field-wrapper.label class="text-xs text-gray-500 mt-1">
                    Κατηγορία
                </x-filament::field-wrapper.label>
            </div>

            <div class="md:col-span-1 flex items-start">
                <x-filament::button
                    wire:click="resetFilters"
                    color="gray"
                    outlined
                    icon="heroicon-o-arrow-path"
                    class="w-full"
                >
                    Επαναφορά
                </x-filament::button>
            </div>
        </div>
    </x-filament::section>
</div>
