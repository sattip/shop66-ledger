<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-10 h-10 bg-blue-500/20 rounded-lg">
                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Απόδοση & Τάσεις</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Ανάπτυξη και τάση χρόνου</p>
                </div>
            </div>
        </x-slot>

        @php
            $metrics = $this->getPerformanceMetrics();
            $trendData = $this->getTrendData();
            $widgetId = uniqid('performance_');
        @endphp

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6" x-data="{
            widgetId: '{{ $widgetId }}',
            trendData: @js($trendData),
            init() {
                this.$nextTick(() => {
                    this.createChart();
                });
            },
            createChart() {
                const ctx = document.getElementById('trendChart_' + this.widgetId);
                if (!ctx || typeof Chart === 'undefined') return;

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: this.trendData.labels || [],
                        datasets: [{
                            label: 'Έσοδα',
                            data: this.trendData.income || [],
                            borderColor: 'rgb(34, 197, 94)',
                            backgroundColor: 'rgba(34, 197, 94, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointRadius: 4,
                            pointHoverRadius: 6
                        }, {
                            label: 'Έξοδα',
                            data: this.trendData.expense || [],
                            borderColor: 'rgb(239, 68, 68)',
                            backgroundColor: 'rgba(239, 68, 68, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointRadius: 4,
                            pointHoverRadius: 6
                        }, {
                            label: 'Καθαρό Αποτέλεσμα',
                            data: this.trendData.net_result || [],
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointRadius: 4,
                            pointHoverRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                                labels: {
                                    color: '#9ca3af',
                                    padding: 15,
                                    font: { size: 12 }
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(17, 24, 39, 0.95)',
                                padding: 12,
                                titleColor: '#f3f4f6',
                                bodyColor: '#f3f4f6',
                                borderColor: '#374151',
                                borderWidth: 1,
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': €' + context.parsed.y.toLocaleString();
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(75, 85, 99, 0.3)'
                                },
                                ticks: {
                                    color: '#9ca3af',
                                    callback: function(value) {
                                        return '€' + value.toLocaleString();
                                    }
                                }
                            },
                            x: {
                                grid: {
                                    color: 'rgba(75, 85, 99, 0.3)'
                                },
                                ticks: {
                                    color: '#9ca3af'
                                }
                            }
                        }
                    }
                });
            }
        }">
            {{-- Growth Cards --}}
            <div class="space-y-4">
                {{-- Revenue Growth Card --}}
                <div class="bg-gradient-to-br from-green-500/10 to-green-600/10 border border-green-500/20 rounded-xl p-6 hover:shadow-lg hover:shadow-green-500/10 transition-all">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-400 mb-1">Ανάπτυξη Εσόδων</p>
                            <div class="flex items-baseline gap-2">
                                <span class="text-3xl font-bold {{ $metrics['revenue_growth'] >= 0 ? 'text-green-400' : 'text-red-400' }}">
                                    {{ $metrics['revenue_growth'] >= 0 ? '+' : '' }}{{ number_format($metrics['revenue_growth'], 1) }}%
                                </span>
                                @if($metrics['revenue_growth'] >= 0)
                                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                                    </svg>
                                @else
                                    <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                                    </svg>
                                @endif
                            </div>
                            <p class="text-xs text-gray-500 mt-1">vs προηγούμενη περίοδο</p>
                        </div>
                        <div class="flex items-center justify-center w-12 h-12 bg-green-500/20 rounded-lg">
                            <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Expense Growth Card --}}
                <div class="bg-gradient-to-br from-red-500/10 to-red-600/10 border border-red-500/20 rounded-xl p-6 hover:shadow-lg hover:shadow-red-500/10 transition-all">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-400 mb-1">Ανάπτυξη Εξόδων</p>
                            <div class="flex items-baseline gap-2">
                                <span class="text-3xl font-bold {{ $metrics['expense_growth'] <= 0 ? 'text-green-400' : 'text-red-400' }}">
                                    {{ $metrics['expense_growth'] >= 0 ? '+' : '' }}{{ number_format($metrics['expense_growth'], 1) }}%
                                </span>
                                @if($metrics['expense_growth'] > 0)
                                    <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                                    </svg>
                                @else
                                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                                    </svg>
                                @endif
                            </div>
                            <p class="text-xs text-gray-500 mt-1">vs προηγούμενη περίοδο</p>
                        </div>
                        <div class="flex items-center justify-center w-12 h-12 bg-red-500/20 rounded-lg">
                            <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Trend Chart --}}
            <div class="lg:col-span-2 bg-gradient-to-br from-gray-800 to-gray-900 p-6 rounded-xl border border-gray-700 shadow-lg">
                <h4 class="text-sm font-semibold text-gray-300 mb-4">Τάση Χρόνου</h4>
                <div style="height: 300px;">
                    <canvas :id="'trendChart_' + widgetId"></canvas>
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
