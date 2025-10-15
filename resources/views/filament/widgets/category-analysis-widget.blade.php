<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-10 h-10 bg-indigo-500/20 rounded-lg">
                    <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Ανάλυση ανά Κατηγορία</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Κατανομή συναλλαγών ανά κατηγορία</p>
                </div>
            </div>
        </x-slot>

        @php
            $breakdown = $this->getCategoryBreakdown();
            $topCategories = $this->getTopCategories();
            $widgetId = uniqid('category_analysis_');
        @endphp

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6" x-data="{
            widgetId: '{{ $widgetId }}',
            breakdownData: @js($breakdown),
            init() {
                this.$nextTick(() => {
                    this.createChart();
                });
            },
            createChart() {
                const ctx = document.getElementById('categoryPieChart_' + this.widgetId);
                if (!ctx || typeof Chart === 'undefined') return;

                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: this.breakdownData.labels || [],
                        datasets: [{
                            data: this.breakdownData.data || [],
                            backgroundColor: this.breakdownData.colors || [],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'right',
                                labels: {
                                    boxWidth: 12,
                                    font: { size: 11 },
                                    color: '#9ca3af',
                                    padding: 10
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.parsed || 0;
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = ((value / total) * 100).toFixed(1);
                                        return label + ': €' + value.toLocaleString() + ' (' + percentage + '%)';
                                    }
                                }
                            }
                        }
                    }
                });
            }
        }">
            {{-- Pie Chart --}}
            <div class="bg-gradient-to-br from-gray-800 to-gray-900 p-6 rounded-xl border border-gray-700 shadow-lg">
                <h4 class="text-sm font-semibold text-gray-300 mb-4">Κατανομή ανά Κατηγορία</h4>
                <div style="height: 250px;">
                    <canvas :id="'categoryPieChart_' + widgetId"></canvas>
                </div>
            </div>

            {{-- Top 5 Revenue --}}
            <div class="bg-gradient-to-br from-gray-800 to-gray-900 p-6 rounded-xl border border-gray-700 shadow-lg">
                <h4 class="text-sm font-semibold text-gray-300 mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                    Top 5 Κατηγορίες (Έσοδα)
                </h4>
                <div class="space-y-3">
                    @forelse($topCategories['revenue'] as $index => $item)
                        <div class="flex items-center justify-between p-3 bg-gray-700/50 rounded-lg hover:bg-gray-700 transition-colors">
                            <div class="flex items-center gap-3">
                                <span class="flex items-center justify-center w-6 h-6 bg-green-500/20 text-green-400 text-xs font-bold rounded">{{ $index + 1 }}</span>
                                <span class="text-sm font-medium text-gray-200">{{ $item['name'] }}</span>
                            </div>
                            <span class="text-sm font-bold text-green-400">€{{ number_format($item['total'], 0) }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 text-center py-8">Δεν υπάρχουν δεδομένα</p>
                    @endforelse
                </div>
            </div>

            {{-- Top 5 Expenses --}}
            <div class="bg-gradient-to-br from-gray-800 to-gray-900 p-6 rounded-xl border border-gray-700 shadow-lg">
                <h4 class="text-sm font-semibold text-gray-300 mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                    </svg>
                    Top 5 Κατηγορίες (Έξοδα)
                </h4>
                <div class="space-y-3">
                    @forelse($topCategories['expenses'] as $index => $item)
                        <div class="flex items-center justify-between p-3 bg-gray-700/50 rounded-lg hover:bg-gray-700 transition-colors">
                            <div class="flex items-center gap-3">
                                <span class="flex items-center justify-center w-6 h-6 bg-red-500/20 text-red-400 text-xs font-bold rounded">{{ $index + 1 }}</span>
                                <span class="text-sm font-medium text-gray-200">{{ $item['name'] }}</span>
                            </div>
                            <span class="text-sm font-bold text-red-400">€{{ number_format($item['total'], 0) }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 text-center py-8">Δεν υπάρχουν δεδομένα</p>
                    @endforelse
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
