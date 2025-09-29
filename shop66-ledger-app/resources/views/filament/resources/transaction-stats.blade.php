<div class="space-y-4">
    <div class="grid grid-cols-2 gap-4">
        <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
            <div class="text-sm text-green-600 dark:text-green-400 font-medium">Σύνολο Εσόδων</div>
            <div class="text-2xl font-bold text-green-700 dark:text-green-300">
                € {{ number_format($totalIncome, 2, ',', '.') }}
            </div>
        </div>

        <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-4">
            <div class="text-sm text-red-600 dark:text-red-400 font-medium">Σύνολο Εξόδων</div>
            <div class="text-2xl font-bold text-red-700 dark:text-red-300">
                € {{ number_format($totalExpense, 2, ',', '.') }}
            </div>
        </div>
    </div>

    <div class="{{ $balance >= 0 ? 'bg-green-50 dark:bg-green-900/20' : 'bg-red-50 dark:bg-red-900/20' }} rounded-lg p-4">
        <div class="text-sm {{ $balance >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }} font-medium">Υπόλοιπο</div>
        <div class="text-3xl font-bold {{ $balance >= 0 ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300' }}">
            € {{ number_format($balance, 2, ',', '.') }}
        </div>
    </div>

    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
        <div class="text-sm text-blue-600 dark:text-blue-400 font-medium">Σύνολο Συναλλαγών</div>
        <div class="text-2xl font-bold text-blue-700 dark:text-blue-300">
            {{ $count }}
        </div>
    </div>

    <div class="text-xs text-gray-500 dark:text-gray-400 text-center mt-4">
        * Τα στατιστικά αφορούν μόνο τις φιλτραρισμένες εγγραφές
    </div>
</div>