<x-filament-widgets::widget>
    <div x-data="{ filtersOpen: true }">
        {{-- Modern Header with Collapsible Filters --}}
        <div class="bg-gradient-to-r from-gray-800 to-gray-900 dark:from-gray-900 dark:to-black rounded-t-xl p-6 border border-gray-700 dark:border-gray-800">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex items-center justify-center w-12 h-12 bg-primary-500/20 rounded-lg">
                        <svg class="w-6 h-6 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-white">Φίλτρα & Αναφορές</h2>
                        <p class="text-sm text-gray-400">Προβολή αναλυτικών στοιχείων συναλλαγών</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    {{-- Export Buttons --}}
                    <div class="flex gap-2">
                        <button wire:click="exportCSV" class="inline-flex items-center gap-2 px-3 py-2 bg-gray-700 hover:bg-gray-600 text-white text-sm font-medium rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            CSV
                        </button>
                        <button wire:click="exportExcel" class="inline-flex items-center gap-2 px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Excel
                        </button>
                        <button wire:click="exportPDF" class="inline-flex items-center gap-2 px-3 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                            PDF
                        </button>
                    </div>

                    {{-- Collapse Toggle --}}
                    <button @click="filtersOpen = !filtersOpen" class="p-2 hover:bg-gray-700 rounded-lg transition-colors">
                        <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': !filtersOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Filters Panel --}}
            <div x-show="filtersOpen" x-collapse class="mt-6 pt-6 border-t border-gray-700">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Από Ημερομηνία</label>
                        <x-filament::input.wrapper class="bg-gray-800 border-gray-600">
                            <x-filament::input
                                type="date"
                                wire:model.live="filters.date_from"
                                class="bg-gray-800 text-white border-0"
                            />
                        </x-filament::input.wrapper>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Έως Ημερομηνία</label>
                        <x-filament::input.wrapper class="bg-gray-800 border-gray-600">
                            <x-filament::input
                                type="date"
                                wire:model.live="filters.date_to"
                                class="bg-gray-800 text-white border-0"
                            />
                        </x-filament::input.wrapper>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Κατάστημα</label>
                        <x-filament::input.wrapper class="bg-gray-800 border-gray-600">
                            <x-filament::input.select wire:model.live="filters.store_id" class="bg-gray-800 text-white border-0">
                                <option value="all">Όλα τα Καταστήματα</option>
                                @foreach($this->getStores() as $store)
                                    <option value="{{ $store->id }}">{{ $store->name }}</option>
                                @endforeach
                            </x-filament::input.select>
                        </x-filament::input.wrapper>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Τύπος</label>
                        <x-filament::input.wrapper class="bg-gray-800 border-gray-600">
                            <x-filament::input.select wire:model.live="filters.type" class="bg-gray-800 text-white border-0">
                                <option value="all">Όλα</option>
                                <option value="income">Έσοδα</option>
                                <option value="expense">Έξοδα</option>
                            </x-filament::input.select>
                        </x-filament::input.wrapper>
                    </div>

                    <div class="lg:col-span-2">
                        <label class="block text-sm font-medium text-gray-300 mb-2">Κατηγορία</label>
                        <x-filament::input.wrapper class="bg-gray-800 border-gray-600">
                            <x-filament::input.select wire:model.live="filters.category_id" class="bg-gray-800 text-white border-0">
                                <option value="all">Όλες οι Κατηγορίες</option>
                                @foreach($this->getCategories() as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }} ({{ $category->type === 'income' ? 'Έσοδο' : 'Έξοδο' }})</option>
                                @endforeach
                            </x-filament::input.select>
                        </x-filament::input.wrapper>
                    </div>
                </div>

                <div class="mt-4 flex justify-end">
                    <button wire:click="resetFilters" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white text-sm font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Επαναφορά Φίλτρων
                    </button>
                </div>
            </div>
        </div>

        @php
            $summary = $this->getSummary();
        @endphp

        {{-- Enhanced KPI Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
            {{-- Total Revenue Card --}}
            <div class="relative overflow-hidden bg-gradient-to-br from-green-500/20 to-emerald-600/20 border border-green-500/30 rounded-xl p-6 hover:shadow-lg hover:shadow-green-500/20 transition-all group">
                <div class="absolute inset-0 bg-gradient-to-br from-green-400/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-400 mb-1">Συνολικά Έσοδα</p>
                        <h3 class="text-3xl font-bold text-green-400 mt-2">
                            €{{ number_format($summary['income'], 2, ',', '.') }}
                        </h3>
                    </div>
                    <div class="flex items-center justify-center w-12 h-12 bg-green-500/30 rounded-lg group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                </div>
                </div>
            </div>

            {{-- Total Expenses Card --}}
            <div class="relative overflow-hidden bg-gradient-to-br from-red-500/20 to-rose-600/20 border border-red-500/30 rounded-xl p-6 hover:shadow-lg hover:shadow-red-500/20 transition-all group">
                <div class="absolute inset-0 bg-gradient-to-br from-red-400/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-400 mb-1">Συνολικά Έξοδα</p>
                        <h3 class="text-3xl font-bold text-red-400 mt-2">
                            €{{ number_format($summary['expense'], 2, ',', '.') }}
                        </h3>
                    </div>
                    <div class="flex items-center justify-center w-12 h-12 bg-red-500/30 rounded-lg group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                        </svg>
                    </div>
                </div>
                </div>
            </div>

            {{-- Net Profit Card --}}
            <div class="relative overflow-hidden bg-gradient-to-br {{ $summary['balance'] >= 0 ? 'from-blue-500/20 to-cyan-600/20 border-blue-500/30' : 'from-red-500/20 to-rose-600/20 border-red-500/30' }} border rounded-xl p-6 hover:shadow-lg {{ $summary['balance'] >= 0 ? 'hover:shadow-blue-500/20' : 'hover:shadow-red-500/20' }} transition-all group">
                <div class="absolute inset-0 bg-gradient-to-br {{ $summary['balance'] >= 0 ? 'from-blue-400/5' : 'from-red-400/5' }} to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-400 mb-1">Καθαρό Αποτέλεσμα</p>
                        <h3 class="text-3xl font-bold {{ $summary['balance'] >= 0 ? 'text-blue-400' : 'text-red-400' }} mt-2">
                            €{{ number_format($summary['balance'], 2, ',', '.') }}
                        </h3>
                    </div>
                    <div class="flex items-center justify-center w-12 h-12 {{ $summary['balance'] >= 0 ? 'bg-blue-500/30' : 'bg-red-500/30' }} rounded-lg group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 {{ $summary['balance'] >= 0 ? 'text-blue-300' : 'text-red-300' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                </div>
            </div>

            {{-- Profit Margin Card --}}
            <div class="relative overflow-hidden bg-gradient-to-br {{ $summary['profit_margin'] >= 0 ? 'from-purple-500/20 to-fuchsia-600/20 border-purple-500/30' : 'from-red-500/20 to-rose-600/20 border-red-500/30' }} border rounded-xl p-6 hover:shadow-lg {{ $summary['profit_margin'] >= 0 ? 'hover:shadow-purple-500/20' : 'hover:shadow-red-500/20' }} transition-all group">
                <div class="absolute inset-0 bg-gradient-to-br {{ $summary['profit_margin'] >= 0 ? 'from-purple-400/5' : 'from-red-400/5' }} to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-400 mb-1">Περιθώριο Κέρδους</p>
                        <h3 class="text-3xl font-bold {{ $summary['profit_margin'] >= 0 ? 'text-purple-400' : 'text-red-400' }} mt-2">
                            {{ number_format($summary['profit_margin'], 1) }}%
                        </h3>
                    </div>
                    <div class="flex items-center justify-center w-12 h-12 {{ $summary['profit_margin'] >= 0 ? 'bg-purple-500/30' : 'bg-red-500/30' }} rounded-lg group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 {{ $summary['profit_margin'] >= 0 ? 'text-purple-300' : 'text-red-300' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 00 2-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
