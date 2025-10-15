<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                </svg>
                <span class="text-lg font-semibold">Γρήγορες Πληροφορίες</span>
            </div>
        </x-slot>

        @php
            $insights = $this->getInsights();
        @endphp

        @if (count($insights) > 0)
            <div class="space-y-3">
                @foreach ($insights as $insight)
                    <div class="flex items-start gap-3 p-3 rounded-lg {{ 
                        $insight['type'] === 'success' ? 'bg-green-50 dark:bg-green-900/20' :
                        ($insight['type'] === 'warning' ? 'bg-yellow-50 dark:bg-yellow-900/20' :
                        ($insight['type'] === 'danger' ? 'bg-red-50 dark:bg-red-900/20' : 'bg-blue-50 dark:bg-blue-900/20'))
                    }}">
                        <div class="mt-0.5">
                            <x-filament::icon
                                :icon="$insight['icon']"
                                class="w-5 h-5 {{ 
                                    $insight['type'] === 'success' ? 'text-green-600 dark:text-green-400' :
                                    ($insight['type'] === 'warning' ? 'text-yellow-600 dark:text-yellow-400' :
                                    ($insight['type'] === 'danger' ? 'text-red-600 dark:text-red-400' : 'text-blue-600 dark:text-blue-400'))
                                }}"
                            />
                        </div>
                        <p class="text-sm {{ 
                            $insight['type'] === 'success' ? 'text-green-700 dark:text-green-300' :
                            ($insight['type'] === 'warning' ? 'text-yellow-700 dark:text-yellow-300' :
                            ($insight['type'] === 'danger' ? 'text-red-700 dark:text-red-300' : 'text-blue-700 dark:text-blue-300'))
                        }}">
                            {{ $insight['message'] }}
                        </p>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-6 text-gray-500 dark:text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p>Δεν υπάρχουν διαθέσιμες πληροφορίες αυτή τη στιγμή</p>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
