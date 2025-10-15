{{-- Store Selector Header --}}
<div class="mb-6">
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 dark:from-blue-800 dark:to-blue-950 rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-12 h-12 bg-white/10 backdrop-blur-sm rounded-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-white font-semibold text-lg">Επιλογή Καταστήματος</h3>
                    <p class="text-blue-100 text-sm">Προβολή αναλυτικών στοιχείων ανά κατάστημα</p>
                </div>
            </div>
            <div class="min-w-[280px]">
                <form wire:submit.prevent="submit">
                    {{ $form }}
                </form>
            </div>
        </div>
    </div>
</div>
