<?php

namespace Database\Seeders;

use App\Models\Store;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Get or create a default store for permissions
        $store = Store::first();
        if (! $store) {
            $store = Store::create([
                'name' => 'Shop66 Main Store',
                'code' => 'MAIN',
                'address_line1' => 'Athens, Greece',
                'city' => 'Athens',
                'state' => 'Attica',
                'country_code' => 'GR',
                'postal_code' => '10000',
                'contact_phone' => '+30 210 1234567',
                'contact_email' => 'info@shop66.gr',
                'currency_code' => 'EUR',
                'timezone' => 'Europe/Athens',
                'default_tax_rate' => 24,
            ]);
            $this->command->info('Default store created for permissions.');
        }

        // Set the team context for all permission operations
        app(PermissionRegistrar::class)->setPermissionsTeamId($store->id);

        // Define resources and their permissions
        $resources = [
            'stores' => 'Καταστήματα',
            'vendors' => 'Προμηθευτές',
            'customers' => 'Πελάτες',
            'invoices' => 'Τιμολόγια',
            'income' => 'Έσοδα',
            'expenses' => 'Έξοδα',
            'users' => 'Χρήστες',
            'roles' => 'Ρόλοι',
            'permissions' => 'Δικαιώματα',
            'analytics' => 'Στατιστικά',
        ];

        $actions = [
            'view' => 'Προβολή',
            'create' => 'Δημιουργία',
            'edit' => 'Επεξεργασία',
            'delete' => 'Διαγραφή',
        ];

        // Create permissions for each resource
        foreach ($resources as $resource => $resourceLabel) {
            foreach ($actions as $action => $actionLabel) {
                // Skip create/edit/delete for analytics (view only)
                if ($resource === 'analytics' && $action !== 'view') {
                    continue;
                }

                Permission::firstOrCreate([
                    'name' => "{$action}-{$resource}",
                    'guard_name' => 'web',
                ]);
            }
        }

        // Create additional special permissions
        $specialPermissions = [
            'access-admin-panel' => 'Πρόσβαση στον Πίνακα Διαχείρισης',
            'export-data' => 'Εξαγωγή Δεδομένων',
            'import-data' => 'Εισαγωγή Δεδομένων',
            'manage-settings' => 'Διαχείριση Ρυθμίσεων',
            'view-reports' => 'Προβολή Αναφορών',
            'manage-backups' => 'Διαχείριση Αντιγράφων Ασφαλείας',
        ];

        foreach ($specialPermissions as $permission => $label) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        // Create default roles if they don't exist
        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        $managerRole = Role::firstOrCreate([
            'name' => 'manager',
            'guard_name' => 'web',
        ]);

        $employeeRole = Role::firstOrCreate([
            'name' => 'employee',
            'guard_name' => 'web',
        ]);

        $viewerRole = Role::firstOrCreate([
            'name' => 'viewer',
            'guard_name' => 'web',
        ]);

        // Assign all permissions to admin
        $adminRole->syncPermissions(Permission::all());

        // Assign manager permissions (everything except user/role/permission management)
        $managerPermissions = Permission::where(function ($query) {
            $query->where('name', 'not like', '%users%')
                ->where('name', 'not like', '%roles%')
                ->where('name', 'not like', '%permissions%');
        })->orWhere('name', 'view-users')->get();

        $managerRole->syncPermissions($managerPermissions);

        // Assign employee permissions (create and edit for business operations)
        $employeePermissions = Permission::where(function ($query) {
            $query->whereIn('name', [
                'view-stores',
                'view-vendors',
                'create-vendors',
                'edit-vendors',
                'view-customers',
                'create-customers',
                'edit-customers',
                'view-invoices',
                'create-invoices',
                'edit-invoices',
                'view-income',
                'create-income',
                'edit-income',
                'view-expenses',
                'create-expenses',
                'edit-expenses',
                'view-analytics',
                'access-admin-panel',
                'view-reports',
            ]);
        })->get();

        $employeeRole->syncPermissions($employeePermissions);

        // Assign viewer permissions (read-only access)
        $viewerPermissions = Permission::where('name', 'like', 'view-%')
            ->orWhere('name', 'access-admin-panel')
            ->get();

        $viewerRole->syncPermissions($viewerPermissions);

        // Apply same permissions to all existing stores
        $allStores = Store::all();
        foreach ($allStores as $eachStore) {
            if ($eachStore->id === $store->id) {
                continue; // Skip the first store we already handled
            }

            // Set team context for this store
            app(PermissionRegistrar::class)->setPermissionsTeamId($eachStore->id);

            // Sync all roles and permissions for this store
            foreach ([$adminRole, $managerRole, $employeeRole, $viewerRole] as $role) {
                // Get the permissions assigned to this role in the first store
                app(PermissionRegistrar::class)->setPermissionsTeamId($store->id);
                $rolePermissions = $role->permissions;

                // Switch to current store and sync permissions
                app(PermissionRegistrar::class)->setPermissionsTeamId($eachStore->id);
                $role->syncPermissions($rolePermissions);
            }

            $this->command->info("Permissions synced for store: {$eachStore->name}");
        }

        $this->command->info('Permissions and roles created successfully!');
        $this->command->table(
            ['Permission', 'Guard'],
            Permission::select('name', 'guard_name')->get()->toArray()
        );
    }
}
