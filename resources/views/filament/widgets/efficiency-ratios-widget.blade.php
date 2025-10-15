<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-10 h-10 bg-cyan-500/20 rounded-lg">
                    <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 00 2-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Αποδοτικότητα & Δείκτες</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Βασικοί δείκτες απόδοσης επιχείρησης</p>
                </div>
            </div>
        </x-slot>

        @php
            $metrics = $this->getEfficiencyMetrics();
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            {{-- Expenses as % of Revenue --}}
            <div class="bg-gradient-to-br from-gray-800 to-gray-900 p-6 rounded-xl border border-gray-700 shadow-lg hover:shadow-xl transition-all">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center justify-center w-10 h-10 rounded-lg {{ $metrics['expense_ratio'] <= 70 ? 'bg-green-500/20' : ($metrics['expense_ratio'] <= 85 ? 'bg-yellow-500/20' : 'bg-red-500/20') }}">
                        <svg class="w-5 h-5 {{ $metrics['expense_ratio'] <= 70 ? 'text-green-400' : ($metrics['expense_ratio'] <= 85 ? 'text-yellow-400' : 'text-red-400') }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    @if($metrics['expense_ratio'] <= 70)
                        <span class="text-xs font-medium px-2 py-1 bg-green-500/20 text-green-400 rounded-full">Εξαιρετικά</span>
                    @elseif($metrics['expense_ratio'] <= 85)
                        <span class="text-xs font-medium px-2 py-1 bg-yellow-500/20 text-yellow-400 rounded-full">Καλά</span>
                    @else
                        <span class="text-xs font-medium px-2 py-1 bg-red-500/20 text-red-400 rounded-full">Προσοχή</span>
                    @endif
                </div>
                <p class="text-sm text-gray-400 mb-2">Έξοδα ως % Εσόδων</p>
                <p class="text-3xl font-bold {{ $metrics['expense_ratio'] <= 70 ? 'text-green-400' : ($metrics['expense_ratio'] <= 85 ? 'text-yellow-400' : 'text-red-400') }}">
                    {{ number_format($metrics['expense_ratio'], 1) }}%
                </p>
                <div class="w-full bg-gray-700 rounded-full h-2 mt-4">
                    <div class="h-2 rounded-full {{ $metrics['expense_ratio'] <= 70 ? 'bg-green-500' : ($metrics['expense_ratio'] <= 85 ? 'bg-yellow-500' : 'bg-red-500') }}"
                         style="width: {{ min($metrics['expense_ratio'], 100) }}%"></div>
                </div>
            </div>

            {{-- Revenue per Store --}}
            <div class="bg-gradient-to-br from-gray-800 to-gray-900 p-6 rounded-xl border border-gray-700 shadow-lg hover:shadow-xl transition-all">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center justify-center w-10 h-10 bg-blue-500/20 rounded-lg">
                        <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <span class="text-xs font-medium px-2 py-1 bg-blue-500/20 text-blue-400 rounded-full">
                        {{ $metrics['store_count'] }} {{ $metrics['store_count'] === 1 ? 'κατάστημα' : 'καταστήματα' }}
                    </span>
                </div>
                <p class="text-sm text-gray-400 mb-2">Έσοδα ανά Κατάστημα</p>
                <p class="text-3xl font-bold text-blue-400">
                    €{{ number_format($metrics['revenue_per_store'], 2, ',', '.') }}
                </p>
                <p class="text-xs text-gray-500 mt-2">
                    Μέσος όρος περιόδου
                </p>
            </div>

            {{-- Average Daily Revenue --}}
            <div class="bg-gradient-to-br from-gray-800 to-gray-900 p-6 rounded-xl border border-gray-700 shadow-lg hover:shadow-xl transition-all">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center justify-center w-10 h-10 bg-purple-500/20 rounded-lg">
                        <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div class="flex items-center gap-1">
                        <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                        <span class="text-xs text-gray-400">Ημερήσια</span>
                    </div>
                </div>
                <p class="text-sm text-gray-400 mb-2">Μέσο Ημερήσιο Έσοδο</p>
                <p class="text-3xl font-bold text-purple-400">
                    €{{ number_format($metrics['avg_daily_revenue'], 2, ',', '.') }}
                </p>
                <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-700">
                    <span class="text-xs text-gray-500">Ημερήσιο Έξοδο</span>
                    <span class="text-sm font-semibold text-red-400">€{{ number_format($metrics['avg_daily_expense'], 2, ',', '.') }}</span>
                </div>
            </div>

            {{-- Break-Even Point --}}
            <div class="bg-gradient-to-br from-gray-800 to-gray-900 p-6 rounded-xl border border-gray-700 shadow-lg hover:shadow-xl transition-all">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center justify-center w-10 h-10 {{ $metrics['break_even_date'] ? 'bg-green-500/20' : 'bg-orange-500/20' }} rounded-lg">
                        @if($metrics['break_even_date'])
                            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        @else
                            <svg class="w-5 h-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        @endif
                    </div>
                    @if($metrics['break_even_date'])
                        <span class="text-xs font-medium px-2 py-1 bg-green-500/20 text-green-400 rounded-full flex items-center gap-1">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            Επιτεύχθηκε
                        </span>
                    @else
                        <span class="text-xs font-medium px-2 py-1 bg-orange-500/20 text-orange-400 rounded-full">Σε εξέλιξη</span>
                    @endif
                </div>
                <p class="text-sm text-gray-400 mb-2">Σημείο Νεκρού</p>
                @if($metrics['break_even_date'])
                    <p class="text-2xl font-bold text-green-400">
                        {{ $metrics['break_even_date'] }}
                    </p>
                    <p class="text-xs text-gray-500 mt-2">Ημερομηνία επίτευξης</p>
                @else
                    <p class="text-2xl font-bold text-orange-400">
                        Δεν επιτεύχθηκε
                    </p>
                    <p class="text-xs text-gray-500 mt-2">Τρέχουσα περίοδος</p>
                @endif
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
