<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Store;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ComprehensiveCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Income Categories (Κατηγορίες Εσόδων)
        $incomeCategories = [
            ['name' => 'Πωλήσεις Προϊόντων', 'display_order' => 1],
            ['name' => 'Πωλήσεις Υπηρεσιών', 'display_order' => 2],
            ['name' => 'Πωλήσεις Online (E-shop)', 'display_order' => 3],
            ['name' => 'Πωλήσεις Χονδρικής', 'display_order' => 4],
            ['name' => 'Πωλήσεις Λιανικής', 'display_order' => 5],
            ['name' => 'Εκπτώσεις & Προσφορές', 'display_order' => 6],
            ['name' => 'Επιστροφές Πελατών', 'display_order' => 7],
            ['name' => 'Προμήθειες', 'display_order' => 8],
            ['name' => 'Επιδοτήσεις', 'display_order' => 9],
            ['name' => 'Τόκοι & Επενδύσεις', 'display_order' => 10],
            ['name' => 'Ενοίκια Εισπρακτέα', 'display_order' => 11],
            ['name' => 'Δωρεές & Χορηγίες (Έσοδα)', 'display_order' => 12],
            ['name' => 'Λοιπά Έσοδα', 'display_order' => 13],
        ];

        // Expense Categories (Κατηγορίες Εξόδων)
        $expenseCategories = [
            // Λειτουργικά Έξοδα
            ['name' => 'Ενοίκιο Καταστήματος', 'display_order' => 1],
            ['name' => 'Κοινόχρηστα', 'display_order' => 2],
            ['name' => 'ΔΕΗ (Ρεύμα)', 'display_order' => 3],
            ['name' => 'ΕΥΔΑΠ (Νερό)', 'display_order' => 4],
            ['name' => 'Τηλεφωνία & Internet', 'display_order' => 5],
            ['name' => 'Θέρμανση', 'display_order' => 6],

            // Προμήθειες & Αγορές
            ['name' => 'Αγορές Εμπορευμάτων', 'display_order' => 10],
            ['name' => 'Αγορές Α\' Υλών', 'display_order' => 11],
            ['name' => 'Αναλώσιμα Υλικά', 'display_order' => 12],
            ['name' => 'Συσκευασίες', 'display_order' => 13],
            ['name' => 'Εξοπλισμός & Μηχανήματα', 'display_order' => 14],

            // Προσωπικό
            ['name' => 'Μισθοδοσία', 'display_order' => 20],
            ['name' => 'Εργοδοτικές Εισφορές', 'display_order' => 21],
            ['name' => 'Δώρα & Επιδόματα', 'display_order' => 22],
            ['name' => 'Εκπαίδευση Προσωπικού', 'display_order' => 23],

            // Μεταφορές & Logistics
            ['name' => 'Μεταφορικά', 'display_order' => 30],
            ['name' => 'Courier (ACS, ΕΛΤΑ, κλπ)', 'display_order' => 31],
            ['name' => 'Καύσιμα & Διόδια', 'display_order' => 32],
            ['name' => 'Συντήρηση Οχημάτων', 'display_order' => 33],

            // Διαφήμιση & Marketing
            ['name' => 'Διαφήμιση Online', 'display_order' => 40],
            ['name' => 'Διαφήμιση Offline', 'display_order' => 41],
            ['name' => 'Social Media Marketing', 'display_order' => 42],
            ['name' => 'Εκτυπώσεις & Διαφημιστικό Υλικό', 'display_order' => 43],
            ['name' => 'Εκδηλώσεις & Προωθητικές Ενέργειες', 'display_order' => 44],

            // Τεχνολογία & Λογισμικό
            ['name' => 'Συνδρομές Λογισμικού', 'display_order' => 50],
            ['name' => 'Hosting & Domain', 'display_order' => 51],
            ['name' => 'Συντήρηση IT', 'display_order' => 52],
            ['name' => 'Αναβάθμιση Συστημάτων', 'display_order' => 53],

            // Επαγγελματικές Υπηρεσίες
            ['name' => 'Λογιστικά', 'display_order' => 60],
            ['name' => 'Νομικές Υπηρεσίες', 'display_order' => 61],
            ['name' => 'Συμβουλευτικές Υπηρεσίες', 'display_order' => 62],
            ['name' => 'Ασφαλιστικές Εισφορές', 'display_order' => 63],

            // Συντήρηση & Επισκευές
            ['name' => 'Συντήρηση Καταστήματος', 'display_order' => 70],
            ['name' => 'Επισκευές & Βελτιώσεις', 'display_order' => 71],
            ['name' => 'Καθαρισμός & Υγιεινή', 'display_order' => 72],

            // Φόροι & Τέλη
            ['name' => 'ΦΠΑ', 'display_order' => 80],
            ['name' => 'Φόρος Εισοδήματος', 'display_order' => 81],
            ['name' => 'ΕΝΦΙΑ', 'display_order' => 82],
            ['name' => 'Τέλη Κυκλοφορίας', 'display_order' => 83],
            ['name' => 'Λοιποί Φόροι & Τέλη', 'display_order' => 84],

            // Τραπεζικά & Χρηματοοικονομικά
            ['name' => 'Τραπεζικά Έξοδα', 'display_order' => 90],
            ['name' => 'Τόκοι Δανείων', 'display_order' => 91],
            ['name' => 'Προμήθειες POS/e-Banking', 'display_order' => 92],

            // Γραφική Ύλη & Αναλώσιμα Γραφείου
            ['name' => 'Γραφική Ύλη', 'display_order' => 100],
            ['name' => 'Έντυπα & Φόρμες', 'display_order' => 101],

            // Άλλα
            ['name' => 'Φιλοξενία & Εστίαση', 'display_order' => 110],
            ['name' => 'Ταξίδια & Διαμονή', 'display_order' => 111],
            ['name' => 'Δωρεές & Χορηγίες (Έξοδα)', 'display_order' => 112],
            ['name' => 'Λοιπά Έξοδα', 'display_order' => 113],
        ];

        // Get all stores
        $stores = Store::all();

        foreach ($stores as $store) {
            $this->command->info("Creating categories for store: {$store->name}");

            // Create Income Categories
            foreach ($incomeCategories as $category) {
                Category::firstOrCreate(
                    [
                        'store_id' => $store->id,
                        'name' => $category['name'],
                        'type' => 'income',
                    ],
                    [
                        'slug' => Str::slug($category['name']).'-'.$store->id,
                        'display_order' => $category['display_order'],
                        'is_active' => true,
                    ]
                );
            }

            // Create Expense Categories
            foreach ($expenseCategories as $category) {
                Category::firstOrCreate(
                    [
                        'store_id' => $store->id,
                        'name' => $category['name'],
                        'type' => 'expense',
                    ],
                    [
                        'slug' => Str::slug($category['name']).'-'.$store->id,
                        'display_order' => $category['display_order'],
                        'is_active' => true,
                    ]
                );
            }
        }

        $this->command->info('Categories created successfully!');
        $this->command->table(
            ['Type', 'Count'],
            [
                ['Income Categories', count($incomeCategories)],
                ['Expense Categories', count($expenseCategories)],
                ['Total per Store', count($incomeCategories) + count($expenseCategories)],
                ['Total Stores', $stores->count()],
                ['Grand Total', (count($incomeCategories) + count($expenseCategories)) * $stores->count()],
            ]
        );
    }
}
