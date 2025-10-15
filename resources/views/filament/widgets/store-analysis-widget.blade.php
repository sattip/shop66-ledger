<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-10 h-10 bg-orange-500/20 rounded-lg">
                    <svg class="w-5 h-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Ανάλυση ανά Κατάστημα</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Απόδοση και σύγκριση καταστημάτων</p>
                </div>
            </div>
        </x-slot>

        @php
            $topRevenue = $this->getTopStoresByRevenue();
            $topProfitability = $this->getTopStoresByProfitability();
            $chartData = $this->getStoreChartData();
            $incomeExpenseData = $this->getIncomeExpenseChartData();
            $widgetId = uniqid('store_analysis_');
        @endphp

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6"
             x-data="{
                widgetId: '{{ $widgetId }}',
                incomeExpenseData: @js($incomeExpenseData),
                revenueData: @js($chartData),
                init() {
                    this.$nextTick(() => {
                        this.createIncomeExpenseChart();
                        this.createRevenueChart();
                    });
                },
                createIncomeExpenseChart() {
                    const ctx = document.getElementById('incomeExpenseChart_' + this.widgetId);
                    if (ctx && typeof Chart !== 'undefined') {
                        new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: this.incomeExpenseData.labels || [],
                                datasets: [{
                                    label: 'Έσοδα',
                                    data: this.incomeExpenseData.income || [],
                                    backgroundColor: 'rgba(34, 197, 94, 0.8)',
                                    borderColor: 'rgb(34, 197, 94)',
                                    borderWidth: 0,
                                    borderRadius: 6
                                }, {
                                    label: 'Έξοδα',
                                    data: this.incomeExpenseData.expense || [],
                                    backgroundColor: 'rgba(239, 68, 68, 0.8)',
                                    borderColor: 'rgb(239, 68, 68)',
                                    borderWidth: 0,
                                    borderRadius: 6
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
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
                                        borderWidth: 1
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
                                            display: false
                                        },
                                        ticks: {
                                            color: '#9ca3af'
                                        }
                                    }
                                }
                            }
                        });
                    }
                },
                createRevenueChart() {
                    const ctx = document.getElementById('storeRevenueChart_' + this.widgetId);
                    if (ctx && typeof Chart !== 'undefined') {
                        new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: this.revenueData.labels || [],
                                datasets: [{
                                    label: 'Έσοδα',
                                    data: this.revenueData.data || [],
                                    backgroundColor: 'rgba(59, 130, 246, 0.8)',
                                    borderColor: 'rgb(59, 130, 246)',
                                    borderWidth: 0,
                                    borderRadius: 6
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { display: false },
                                    tooltip: {
                                        backgroundColor: 'rgba(17, 24, 39, 0.95)',
                                        padding: 12,
                                        titleColor: '#f3f4f6',
                                        bodyColor: '#f3f4f6',
                                        borderColor: '#374151',
                                        borderWidth: 1
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
                                            display: false
                                        },
                                        ticks: {
                                            color: '#9ca3af'
                                        }
                                    }
                                }
                            }
                        });
                    }
                }
             }">
            {{-- Bar Chart: Income vs Expense --}}
            <div class="bg-gradient-to-br from-gray-800 to-gray-900 p-6 rounded-xl border border-gray-700 shadow-lg">
                <h4 class="text-sm font-semibold text-gray-300 mb-4">Έσοδα vs Έξοδα ανά Κατάστημα</h4>
                <div style="height: 300px;">
                    <canvas :id="'incomeExpenseChart_' + widgetId"></canvas>
                </div>
            </div>

            {{-- Bar Chart: Top 5 Stores by Revenue --}}
            <div class="bg-gradient-to-br from-gray-800 to-gray-900 p-6 rounded-xl border border-gray-700 shadow-lg">
                <h4 class="text-sm font-semibold text-gray-300 mb-4">Top 5 Καταστήματα (Έσοδα)</h4>
                <div style="height: 300px;">
                    <canvas :id="'storeRevenueChart_' + widgetId"></canvas>
                </div>
            </div>

            {{-- Top 5 Stores by Profitability --}}
            <div class="bg-gradient-to-br from-gray-800 to-gray-900 p-6 rounded-xl border border-gray-700 shadow-lg">
                <h4 class="text-sm font-semibold text-gray-300 mb-4">Top 5 Καταστήματα (Κερδοφορία)</h4>
                <div class="space-y-3">
                    @forelse($topProfitability as $index => $item)
                        <div class="p-4 bg-gray-700/50 rounded-lg hover:bg-gray-700 transition-colors">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-3">
                                    <span class="flex items-center justify-center w-7 h-7 bg-blue-500/20 text-blue-400 text-sm font-bold rounded">{{ $index + 1 }}</span>
                                    <span class="text-sm font-semibold text-gray-200">{{ $item['name'] }}</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <span class="text-lg font-bold {{ $item['profit_margin'] >= 0 ? 'text-green-400' : 'text-red-400' }}">
                                        {{ number_format($item['profit_margin'], 1) }}%
                                    </span>
                                    @if($item['profit_margin'] >= 0)
                                        <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                                        </svg>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <div class="flex items-center gap-4">
                                    <span class="text-green-400">
                                        <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z" clip-rule="evenodd"></path>
                                        </svg>
                                        €{{ number_format($item['income'], 0) }}
                                    </span>
                                    <span class="text-red-400">
                                        <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z" clip-rule="evenodd"></path>
                                        </svg>
                                        €{{ number_format($item['expense'], 0) }}
                                    </span>
                                </div>
                                <span class="font-semibold {{ $item['profit'] >= 0 ? 'text-blue-400' : 'text-red-400' }}">
                                    €{{ number_format($item['profit'], 0) }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 text-center py-8">Δεν υπάρχουν δεδομένα</p>
                    @endforelse
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
