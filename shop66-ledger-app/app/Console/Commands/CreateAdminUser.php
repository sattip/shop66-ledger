<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:admin {--email=admin@shop66.com} {--name=Admin} {--password=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an admin user for Filament';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->option('email');
        $name = $this->option('name');
        $password = $this->option('password') ?: 'password';

        // Check if user exists
        $user = User::where('email', $email)->first();

        if ($user) {
            $this->info("User with email {$email} already exists. Updating role...");
        } else {
            // Get the first store or create a default one
            $store = \App\Models\Store::first();
            if (! $store) {
                $store = \App\Models\Store::create([
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
                $this->info('Default store created.');
            }

            // Create new user
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
                'store_id' => $store->id,
            ]);

            $this->info('Admin user created successfully!');
        }

        // Ensure admin role exists
        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        // Get user's store_id or use the first store
        $storeId = $user->store_id ?? \App\Models\Store::first()?->id;

        if (! $storeId) {
            // Create default store if none exists
            $store = \App\Models\Store::create([
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
            $storeId = $store->id;
            $this->info('Default store created.');
        }

        // Set the team context for role assignment
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($storeId);

        // Assign admin role to user
        if (! $user->hasRole('admin')) {
            $user->assignRole($adminRole);
            $this->info('Admin role assigned to user!');
        } else {
            $this->info('User already has admin role.');
        }

        // Also assign to all stores in the system
        $stores = \App\Models\Store::all();
        foreach ($stores as $store) {
            // Attach user to store if not already attached
            if (! $user->stores()->where('stores.id', $store->id)->exists()) {
                $user->stores()->attach($store->id, ['role' => 'admin']);
                $this->info("User attached to store: {$store->name}");
            }
        }

        $this->info("Email: {$email}");
        if (! User::where('email', $email)->exists()) {
            $this->info("Password: {$password}");
            $this->warn('Please change the password after first login!');
        }

        return 0;
    }
}
