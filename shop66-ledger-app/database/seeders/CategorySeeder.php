<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Store;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $store = Store::first();
        if (! $store) {
            $store = Store::create([
                'name' => 'Shop66 Main Store',
                'address' => 'Athens, Greece',
                'city' => 'Athens',
                'state' => 'Attica',
                'country' => 'GR',
                'postal_code' => '10000',
                'phone' => '+30 210 1234567',
                'email' => 'info@shop66.gr',
                'currency_code' => 'EUR',
                'timezone' => 'Europe/Athens',
                'default_tax_rate' => 24,
            ]);
        }

        // Expense Categories
        $expenseCategories = [
            'Ενοίκιο & Κτίριο' => [
                'type' => 'expense',
                'description' => 'Έξοδα ενοικίου και συντήρησης κτιρίου',
                'children' => [
                    'Ενοίκιο Καταστήματος',
                    'Κοινόχρηστα',
                    'Συντήρηση Κτιρίου',
                    'Ασφάλιση Κτιρίου',
                ],
            ],
            'Μισθοδοσία' => [
                'type' => 'expense',
                'description' => 'Μισθοί και ασφαλιστικές εισφορές',
                'children' => [
                    'Μισθοί Προσωπικού',
                    'Ασφαλιστικές Εισφορές',
                    'Επιδόματα',
                    'Υπερωρίες',
                ],
            ],
            'Λογαριασμοί ΔΕΚΟ' => [
                'type' => 'expense',
                'description' => 'Λογαριασμοί κοινής ωφέλειας',
                'children' => [
                    'Ηλεκτρικό Ρεύμα',
                    'Νερό',
                    'Τηλέφωνο',
                    'Internet',
                    'Κινητή Τηλεφωνία',
                ],
            ],
            'Προμήθειες & Αγορές' => [
                'type' => 'expense',
                'description' => 'Αγορές εμπορευμάτων και υλικών',
                'children' => [
                    'Εμπορεύματα',
                    'Πρώτες Ύλες',
                    'Αναλώσιμα',
                    'Υλικά Συσκευασίας',
                ],
            ],
            'Μεταφορικά' => [
                'type' => 'expense',
                'description' => 'Έξοδα μεταφοράς και διανομής',
                'children' => [
                    'Καύσιμα',
                    'Συντήρηση Οχημάτων',
                    'Ασφάλιση Οχημάτων',
                    'Μεταφορικά Τρίτων',
                ],
            ],
            'Marketing & Διαφήμιση' => [
                'type' => 'expense',
                'description' => 'Έξοδα προώθησης και διαφήμισης',
                'children' => [
                    'Διαφήμιση Online',
                    'Έντυπη Διαφήμιση',
                    'Προωθητικές Ενέργειες',
                    'Social Media',
                ],
            ],
            'Λοιπά Έξοδα' => [
                'type' => 'expense',
                'description' => 'Διάφορα λειτουργικά έξοδα',
                'children' => [
                    'Γραφική Ύλη',
                    'Καθαρισμός',
                    'Λογιστικά',
                    'Νομικές Υπηρεσίες',
                    'Τραπεζικά Έξοδα',
                    'Φόροι & Τέλη',
                ],
            ],
        ];

        // Income Categories
        $incomeCategories = [
            'Πωλήσεις Λιανικής' => [
                'type' => 'income',
                'description' => 'Έσοδα από λιανικές πωλήσεις',
                'children' => [
                    'Πωλήσεις Καταστήματος',
                    'Online Πωλήσεις',
                    'Τηλεφωνικές Παραγγελίες',
                ],
            ],
            'Πωλήσεις Χονδρικής' => [
                'type' => 'income',
                'description' => 'Έσοδα από χονδρικές πωλήσεις',
                'children' => [
                    'B2B Πωλήσεις',
                    'Εξαγωγές',
                ],
            ],
            'Υπηρεσίες' => [
                'type' => 'income',
                'description' => 'Έσοδα από παροχή υπηρεσιών',
                'children' => [
                    'Επισκευές',
                    'Εγκαταστάσεις',
                    'Συμβουλευτικές Υπηρεσίες',
                ],
            ],
            'Λοιπά Έσοδα' => [
                'type' => 'income',
                'description' => 'Διάφορα έσοδα',
                'children' => [
                    'Ενοίκια',
                    'Προμήθειες',
                    'Επιδοτήσεις',
                ],
            ],
        ];

        // Create Expense Categories
        $displayOrder = 0;
        foreach ($expenseCategories as $parentName => $parentData) {
            $parent = Category::create([
                'store_id' => $store->id,
                'name' => $parentName,
                'slug' => Str::slug($parentName),
                'type' => $parentData['type'],
                'description' => $parentData['description'],
                'is_active' => true,
                'is_system' => false,
                'display_order' => $displayOrder++,
            ]);

            foreach ($parentData['children'] as $childName) {
                Category::create([
                    'store_id' => $store->id,
                    'parent_id' => $parent->id,
                    'name' => $childName,
                    'slug' => Str::slug($childName),
                    'type' => $parentData['type'],
                    'is_active' => true,
                    'is_system' => false,
                    'display_order' => $displayOrder++,
                ]);
            }
        }

        // Create Income Categories
        foreach ($incomeCategories as $parentName => $parentData) {
            $parent = Category::create([
                'store_id' => $store->id,
                'name' => $parentName,
                'slug' => Str::slug($parentName),
                'type' => $parentData['type'],
                'description' => $parentData['description'],
                'is_active' => true,
                'is_system' => false,
                'display_order' => $displayOrder++,
            ]);

            foreach ($parentData['children'] as $childName) {
                Category::create([
                    'store_id' => $store->id,
                    'parent_id' => $parent->id,
                    'name' => $childName,
                    'slug' => Str::slug($childName),
                    'type' => $parentData['type'],
                    'is_active' => true,
                    'is_system' => false,
                    'display_order' => $displayOrder++,
                ]);
            }
        }

        $this->command->info('Categories created successfully!');
        $this->command->info('Created '.Category::count().' categories.');
    }
}
