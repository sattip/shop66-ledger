<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Store;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure roles exist
        $this->ensureRolesExist();

        // Create Stores
        $stores = $this->createStores();

        // Create Users with store-specific access
        $users = $this->createUsersForStores($stores);

        // Create Vendors for each store
        $vendors = $this->createVendors($stores);

        // Create Customers for each store
        $customers = $this->createCustomers($stores);

        // Create Categories if not exists
        $this->ensureCategoriesExist($stores);

        // Create Accounts for each store
        $this->createAccounts($stores);

        // Create Invoices for each store
        $this->createInvoices($stores, $vendors);

        // Create Transactions for each store
        $this->createTransactions($stores, $vendors, $customers);

        $this->command->info('✅ Dummy data created successfully!');
        $this->command->table(
            ['Entity', 'Count'],
            [
                ['Stores', Store::count()],
                ['Users', User::count()],
                ['Vendors', Vendor::count()],
                ['Customers', Customer::count()],
                ['Accounts', \App\Models\Account::count()],
                ['Invoices', Invoice::count()],
                ['Transactions', Transaction::count()],
            ]
        );
    }

    private function ensureRolesExist(): void
    {
        $roles = ['admin', 'manager', 'employee', 'viewer'];

        foreach ($roles as $roleName) {
            Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);
        }
    }

    private function createStores(): array
    {
        $storeData = [
            [
                'name' => 'Σούπερ Μάρκετ Αθηνών',
                'code' => 'ATH',
                'address_line1' => 'Ερμού 45',
                'city' => 'Αθήνα',
                'state' => 'Αττική',
                'postal_code' => '10563',
                'contact_phone' => '+30 210 3234567',
                'contact_email' => 'athens@shop66.gr',
                'tax_id' => '999888777',
            ],
            [
                'name' => 'Σούπερ Μάρκετ Θεσσαλονίκης',
                'code' => 'THK',
                'address_line1' => 'Τσιμισκή 120',
                'city' => 'Θεσσαλονίκη',
                'state' => 'Μακεδονία',
                'postal_code' => '54621',
                'contact_phone' => '+30 2310 234567',
                'contact_email' => 'thessaloniki@shop66.gr',
                'tax_id' => '888777666',
            ],
            [
                'name' => 'Σούπερ Μάρκετ Πάτρας',
                'code' => 'PTR',
                'address_line1' => 'Ρήγα Φεραίου 25',
                'city' => 'Πάτρα',
                'state' => 'Αχαΐα',
                'postal_code' => '26221',
                'contact_phone' => '+30 2610 345678',
                'contact_email' => 'patra@shop66.gr',
                'tax_id' => '777666555',
            ],
        ];

        $stores = [];
        foreach ($storeData as $data) {
            $stores[] = Store::firstOrCreate(
                ['contact_email' => $data['contact_email']],
                array_merge($data, [
                    'country_code' => 'GR',
                    'currency_code' => 'EUR',
                    'timezone' => 'Europe/Athens',
                    'default_tax_rate' => 24,
                ])
            );
        }

        return $stores;
    }

    private function createUsersForStores(array $stores): array
    {
        $users = [];

        // Create admin user with access to all stores
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@shop66.gr'],
            [
                'name' => 'Διαχειριστής Συστήματος',
                'password' => Hash::make(app()->environment('production') ? Str::random(16) : 'password'),
                'email_verified_at' => now(),
            ]
        );

        // Set store context and assign admin role
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($stores[0]->id);
        $adminUser->assignRole('admin');

        // Attach admin to all stores
        foreach ($stores as $store) {
            $adminUser->stores()->syncWithoutDetaching([$store->id => ['role' => 'admin']]);
        }
        $users[] = $adminUser;

        // Create managers for each store
        foreach ($stores as $index => $store) {
            $managerEmail = 'manager'.($index + 1).'@shop66.gr';
            $manager = User::firstOrCreate(
                ['email' => $managerEmail],
                [
                    'name' => 'Διευθυντής '.$store->city,
                    'password' => Hash::make(app()->environment('production') ? Str::random(16) : 'password'),
                    'email_verified_at' => now(),
                ]
            );

            // Set store context and assign manager role
            app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($store->id);
            $manager->assignRole('manager');

            // Attach to specific store
            $manager->stores()->syncWithoutDetaching([$store->id => ['role' => 'manager']]);
            $users[] = $manager;

            // Create 2 employees for each store
            for ($i = 1; $i <= 2; $i++) {
                $employeeEmail = 'employee'.($index + 1).'_'.$i.'@shop66.gr';
                $employee = User::firstOrCreate(
                    ['email' => $employeeEmail],
                    [
                        'name' => 'Υπάλληλος '.$i.' '.$store->city,
                        'password' => Hash::make(app()->environment('production') ? Str::random(16) : 'password'),
                        'email_verified_at' => now(),
                    ]
                );

                // Set store context and assign employee role
                app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($store->id);
                $employee->assignRole('employee');

                // Attach to specific store
                $employee->stores()->syncWithoutDetaching([$store->id => ['role' => 'employee']]);
                $users[] = $employee;
            }
        }

        // Create a viewer with access to all stores
        $viewer = User::firstOrCreate(
            ['email' => 'viewer@shop66.gr'],
            [
                'name' => 'Παρατηρητής',
                'password' => Hash::make(app()->environment('production') ? Str::random(16) : 'password'),
                'email_verified_at' => now(),
            ]
        );

        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($stores[0]->id);
        $viewer->assignRole('viewer');

        foreach ($stores as $store) {
            $viewer->stores()->syncWithoutDetaching([$store->id => ['role' => 'viewer']]);
        }
        $users[] = $viewer;

        $this->command->info('Created '.count($users).' users with store-specific access');

        return $users;
    }

    private function createVendors(array $stores): array
    {
        $vendorNames = [
            'Μασούτης Α.Ε. Χονδρική',
            'ΑΒ Βασιλόπουλος Προμήθειες',
            'Σκλαβενίτης Διανομές Ο.Ε.',
            'METRO Cash & Carry ΑΕΒΕ',
            'Κρητικός Τροφοδοσίες Α.Ε.',
            'Γαλαξίας Διανομές Ε.Π.Ε.',
            'Φρέσκο Γάλα Δωδώνη',
            'Αρτοποιεία Παπαδόπουλος & ΣΙΑ',
            'Κρεοπωλείο Γεωργίου Α.Ε.',
            'Οπωροπωλείο Νικολάου Εισαγωγές',
            'Ελληνικά Ποτά Α.Ε.',
            'Αρτοποιεία Βενέτης',
            'Κτηνοτροφική Ηπείρου',
            'Ιχθυοκαλλιέργειες Ανδρεάδης',
            'Οινοποιείο Τσάνταλη',
        ];

        $greekStreets = [
            'Ερμού', 'Σταδίου', 'Πανεπιστημίου', 'Ακαδημίας', 'Πατησίων',
            'Αθηνάς', 'Αγίου Κωνσταντίνου', 'Πειραιώς', 'Λένορμαν', 'Κηφισίας',
        ];

        $vendors = [];
        foreach ($stores as $store) {
            foreach ($vendorNames as $index => $name) {
                $street = $greekStreets[array_rand($greekStreets)];
                $vendors[] = Vendor::firstOrCreate(
                    [
                        'store_id' => $store->id,
                        'email' => 'info'.($index + 1).'.'.strtolower(str_replace(' ', '', $store->city)).'@supplier.gr',
                    ],
                    [
                        'name' => $name,
                        'phone' => '+30 '.rand(210, 2810).' '.rand(1000000, 9999999),
                        'address_line1' => $street.' '.rand(1, 150),
                        'city' => $store->city,
                        'state' => $store->state,
                        'country_code' => 'GR',
                        'postal_code' => $store->postal_code,
                        'tax_id' => '0'.rand(10000000, 99999999),
                        'notes' => 'Προμηθευτής τροφίμων και ποτών για '.$store->name,
                        'is_active' => true,
                    ]
                );
            }
        }

        return $vendors;
    }

    private function createCustomers(array $stores): array
    {
        $customerNames = [
            'Καφετέρια Το Στέκι',
            'Εστιατόριο Η Καλή Γωνιά',
            'Ταβέρνα Ο Μπάμπης',
            'Ξενοδοχείο Ακρόπολις',
            'Mini Market Νύχτα-Μέρα',
            'Κάβα Ποτών Διόνυσος',
            'Αρτοποιείο Η Φούρναρη',
            'Ζαχαροπλαστείο Γλυκά Όνειρα',
            'Εστιατόριο Ελληνικές Γεύσεις',
            'Καφέ Μπαρ Κεντρικό',
            'Ψητοπωλείο Ο Νίκος',
            'Σουβλατζίδικο Το Κύμα',
        ];

        $greekStreets = [
            'Ερμού', 'Σταδίου', 'Πανεπιστημίου', 'Ακαδημίας', 'Πατησίων',
            'Αθηνάς', 'Αγίου Κωνσταντίνου', 'Πειραιώς', 'Λένορμαν', 'Κηφισίας',
        ];

        $customers = [];
        foreach ($stores as $store) {
            foreach ($customerNames as $index => $name) {
                $street = $greekStreets[array_rand($greekStreets)];
                $customers[] = Customer::firstOrCreate(
                    [
                        'store_id' => $store->id,
                        'email' => 'contact'.($index + 1).'.'.strtolower(str_replace(' ', '', $store->city)).'@client.gr',
                    ],
                    [
                        'name' => $name,
                        'phone' => '+30 '.rand(210, 2810).' '.rand(1000000, 9999999),
                        'address_line1' => $street.' '.rand(1, 150),
                        'city' => $store->city,
                        'state' => $store->state,
                        'country_code' => 'GR',
                        'postal_code' => $store->postal_code,
                        'customer_code' => 'ΠΕΛ-'.str_pad($store->id * 100 + $index + 1, 5, '0', STR_PAD_LEFT),
                        'is_active' => true,
                    ]
                );
            }
        }

        return $customers;
    }

    private function ensureCategoriesExist(array $stores): void
    {
        foreach ($stores as $store) {
            $existingCategories = Category::where('store_id', $store->id)->count();

            if ($existingCategories == 0) {
                // Create basic categories for this store
                $categories = [
                    ['name' => 'Πωλήσεις Προϊόντων', 'type' => 'income'],
                    ['name' => 'Υπηρεσίες', 'type' => 'income'],
                    ['name' => 'Αγορές Εμπορευμάτων', 'type' => 'expense'],
                    ['name' => 'Λειτουργικά Έξοδα', 'type' => 'expense'],
                    ['name' => 'Μισθοδοσία', 'type' => 'expense'],
                ];

                foreach ($categories as $index => $cat) {
                    Category::create([
                        'store_id' => $store->id,
                        'name' => $cat['name'],
                        'slug' => \Illuminate\Support\Str::slug($cat['name']),
                        'type' => $cat['type'],
                        'is_active' => true,
                        'is_system' => false,
                        'display_order' => $index,
                    ]);
                }
            }
        }
    }

    private function createInvoices(array $stores, array $vendors): void
    {
        foreach ($stores as $store) {
            $storeVendors = array_filter($vendors, fn ($v) => $v->store_id === $store->id);

            // Create 20 invoices per store
            for ($i = 1; $i <= 20; $i++) {
                $vendor = $storeVendors[array_rand($storeVendors)];
                $invoiceDate = now()->subDays(rand(0, 90));
                $dueDate = $invoiceDate->copy()->addDays(30);
                $invoiceType = rand(0, 1) ? 'simple' : 'detailed';

                $subtotal = rand(100, 5000);
                $taxAmount = $subtotal * 0.24;
                $totalAmount = $subtotal + $taxAmount;

                $invoice = Invoice::create([
                    'store_id' => $store->id,
                    'vendor_id' => $vendor->id,
                    'invoice_number' => 'ΤΙΜ-'.$invoiceDate->format('Y').'-'.str_pad($i + ($store->id * 1000), 5, '0', STR_PAD_LEFT),
                    'invoice_date' => $invoiceDate,
                    'due_date' => $dueDate,
                    'invoice_type' => $invoiceType,
                    'status' => $this->randomInvoiceStatus($invoiceDate, $dueDate),
                    'subtotal' => $subtotal,
                    'discount_amount' => rand(0, 200),
                    'tax_amount' => $taxAmount,
                    'total_amount' => $totalAmount,
                    'notes' => 'Τιμολόγιο αγοράς εμπορευμάτων από '.$vendor->name,
                ]);

                // If detailed invoice, add items
                if ($invoiceType === 'detailed') {
                    $this->createInvoiceItems($invoice);
                }
            }
        }
    }

    private function createInvoiceItems(Invoice $invoice): void
    {
        $products = [
            ['name' => 'Γάλα Φρέσκο 1L', 'price' => 1.50],
            ['name' => 'Ψωμί Τοστ Ολικής', 'price' => 1.20],
            ['name' => 'Φέτα ΠΟΠ 400γρ', 'price' => 6.50],
            ['name' => 'Ελαιόλαδο Extra Virgin 1L', 'price' => 8.90],
            ['name' => 'Μακαρόνια Νο.6 500γρ', 'price' => 1.80],
            ['name' => 'Ντομάτες Βιολογικές (κιλό)', 'price' => 2.30],
            ['name' => 'Πατάτες Κύπρου (κιλό)', 'price' => 1.10],
            ['name' => 'Κοτόπουλο Φρέσκο (κιλό)', 'price' => 5.90],
            ['name' => 'Καφές Ελληνικός 250γρ', 'price' => 4.50],
            ['name' => 'Ζάχαρη Λευκή 1κιλό', 'price' => 1.60],
            ['name' => 'Τυρί Γραβιέρα 300γρ', 'price' => 5.80],
            ['name' => 'Γιαούρτι Στραγγιστό 2% 200γρ', 'price' => 2.20],
            ['name' => 'Αυγά Ελευθέρας Βοσκής (6τεμ)', 'price' => 3.50],
            ['name' => 'Μέλι Ελληνικό 450γρ', 'price' => 9.80],
            ['name' => 'Ρύζι Καρολίνα 500γρ', 'price' => 2.10],
        ];

        $itemCount = rand(5, 12);
        $runningTotal = 0;

        for ($i = 0; $i < $itemCount; $i++) {
            $product = $products[array_rand($products)];
            $quantity = rand(2, 25);
            $unitPrice = $product['price'];
            $subtotal = $quantity * $unitPrice;
            $discountPercent = rand(0, 15);
            $discountAmount = $subtotal * ($discountPercent / 100);
            $taxableAmount = $subtotal - $discountAmount;
            $taxRate = 24;
            $taxAmount = $taxableAmount * ($taxRate / 100);
            $total = $taxableAmount + $taxAmount;

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => $product['name'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'discount_percent' => $discountPercent,
                'discount_amount' => $discountAmount,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'sort_order' => $i,
            ]);

            $runningTotal += $total;
        }

        // Update invoice totals
        $invoice->update([
            'subtotal' => $runningTotal / 1.24,
            'tax_amount' => $runningTotal * 0.24 / 1.24,
            'total_amount' => $runningTotal,
        ]);
    }

    private function createTransactions(array $stores, array $vendors, array $customers): void
    {
        foreach ($stores as $store) {
            $storeVendors = array_filter($vendors, fn ($v) => $v->store_id === $store->id);
            $storeCustomers = array_filter($customers, fn ($c) => $c->store_id === $store->id);
            $categories = Category::where('store_id', $store->id)->get();
            $accounts = \App\Models\Account::where('store_id', $store->id)->get();

            // Create 30 transactions per store
            for ($i = 1; $i <= 30; $i++) {
                $type = rand(0, 1) ? 'income' : 'expense';
                $transactionDate = now()->subDays(rand(0, 60));

                if ($type === 'income') {
                    $customer = $storeCustomers[array_rand($storeCustomers)];
                    $category = $categories->where('type', 'income')->random();
                    $subtotal = rand(50, 2000);

                    // Income usually goes to bank or cash account
                    $account = $accounts->whereIn('type', ['bank', 'cash', 'payment_gateway'])->random();

                    Transaction::create([
                        'store_id' => $store->id,
                        'account_id' => $account->id,
                        'category_id' => $category->id,
                        'customer_id' => $customer->id,
                        'user_id' => User::inRandomOrder()->first()->id,
                        'type' => 'income',
                        'status' => $this->randomTransactionStatus(),
                        'reference' => 'ΕΙΣ-'.$transactionDate->format('Ymd').'-'.str_pad($i, 4, '0', STR_PAD_LEFT),
                        'transaction_date' => $transactionDate,
                        'subtotal' => $subtotal,
                        'tax_total' => $subtotal * 0.24,
                        'total' => $subtotal * 1.24,
                        'memo' => 'Είσπραξη από πώληση προϊόντων - '.$customer->name,
                        'currency_code' => 'EUR',
                        'exchange_rate' => 1,
                    ]);
                } else {
                    $vendor = $storeVendors[array_rand($storeVendors)];
                    $category = $categories->where('type', 'expense')->random();
                    $subtotal = rand(100, 3000);

                    // Expenses usually come from bank, credit card or cash
                    $account = $accounts->whereIn('type', ['bank', 'cash', 'credit_card'])->random();

                    Transaction::create([
                        'store_id' => $store->id,
                        'account_id' => $account->id,
                        'category_id' => $category->id,
                        'vendor_id' => $vendor->id,
                        'user_id' => User::inRandomOrder()->first()->id,
                        'type' => 'expense',
                        'status' => $this->randomTransactionStatus(),
                        'reference' => 'ΕΞΟ-'.$transactionDate->format('Ymd').'-'.str_pad($i, 4, '0', STR_PAD_LEFT),
                        'transaction_date' => $transactionDate,
                        'subtotal' => $subtotal,
                        'tax_total' => $subtotal * 0.24,
                        'total' => $subtotal * 1.24,
                        'memo' => 'Πληρωμή για αγορά εμπορευμάτων - '.$vendor->name,
                        'currency_code' => 'EUR',
                        'exchange_rate' => 1,
                    ]);
                }
            }
        }
    }

    private function createAccounts(array $stores): void
    {
        $greekBanks = [
            'Εθνική Τράπεζα',
            'Alpha Bank',
            'Eurobank',
            'Πειραιώς',
        ];

        foreach ($stores as $store) {
            $bank = $greekBanks[array_rand($greekBanks)];
            $accounts = [
                [
                    'name' => 'Κύριος Λογαριασμός '.$bank,
                    'account_number' => 'GR'.rand(10, 99).rand(1000, 9999).rand(1000, 9999).rand(1000, 9999).rand(1000, 9999).rand(100, 999),
                    'type' => 'bank',
                    'opening_balance' => rand(10000, 80000),
                    'is_primary' => true,
                ],
                [
                    'name' => 'Ταμείο Καταστήματος',
                    'account_number' => 'ΤΑΜ-'.$store->code.'-'.str_pad($store->id, 3, '0', STR_PAD_LEFT),
                    'type' => 'cash',
                    'opening_balance' => rand(1000, 8000),
                    'is_primary' => false,
                ],
                [
                    'name' => 'Επαγγελματική Visa',
                    'account_number' => '****-****-****-'.rand(1000, 9999),
                    'type' => 'credit_card',
                    'opening_balance' => -rand(0, 15000),
                    'is_primary' => false,
                ],
                [
                    'name' => 'Viva Wallet',
                    'account_number' => 'viva-'.strtolower(str_replace(' ', '', $store->city)).'-'.rand(100000, 999999),
                    'type' => 'payment_gateway',
                    'opening_balance' => rand(500, 6000),
                    'is_primary' => false,
                ],
            ];

            foreach ($accounts as $accountData) {
                \App\Models\Account::firstOrCreate(
                    [
                        'store_id' => $store->id,
                        'account_number' => $accountData['account_number'],
                    ],
                    [
                        'name' => $accountData['name'],
                        'slug' => \Illuminate\Support\Str::slug($accountData['name']),
                        'type' => $accountData['type'],
                        'currency_code' => 'EUR',
                        'opening_balance' => $accountData['opening_balance'],
                        'current_balance' => $accountData['opening_balance'] + rand(-2000, 8000),
                        'is_primary' => $accountData['is_primary'],
                        'is_active' => true,
                    ]
                );
            }
        }
    }

    private function randomInvoiceStatus($invoiceDate, $dueDate): string
    {
        $now = now();

        if ($invoiceDate->isFuture()) {
            return 'draft';
        }

        if ($dueDate->isPast() && rand(0, 2) === 0) {
            return 'overdue';
        }

        return collect(['draft', 'pending', 'paid', 'cancelled'])->random();
    }

    private function randomTransactionStatus(): string
    {
        $weights = [
            'draft' => 10,
            'pending' => 20,
            'approved' => 30,
            'posted' => 35,
            'cancelled' => 5,
        ];

        $rand = rand(1, 100);
        $cumulative = 0;

        foreach ($weights as $status => $weight) {
            $cumulative += $weight;
            if ($rand <= $cumulative) {
                return $status;
            }
        }

        return 'pending';
    }
}
