<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Store;
use App\Models\TaxRegion;
use App\Models\User;
use App\Support\StoreContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach (UserRole::cases() as $role) {
            Role::findOrCreate($role->value);
        }

        $taxRegion = TaxRegion::firstOrCreate(
            ['code' => 'US-DEFAULT'],
            [
                'name' => 'United States Default',
                'country_code' => 'US',
                'region' => null,
                'default_rate' => 0,
            ]
        );

        $store = Store::firstOrCreate(
            ['code' => 'MAIN'],
            [
                'name' => 'Main Store',
                'tax_region_id' => $taxRegion->id,
                'currency_code' => 'USD',
                'timezone' => 'UTC',
            ]
        );

        $user = User::firstOrCreate(
            ['email' => 'admin@shop66.test'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
            ]
        );

        $store->users()->syncWithoutDetaching([
            $user->id => ['role' => UserRole::SUPER_ADMIN->value],
        ]);

        /** @var StoreContext $context */
        $context = app(StoreContext::class);
        $context->set(null);
        $user->assignRole(UserRole::SUPER_ADMIN->value);
        $context->set($store->id);
        $user->assignRole(UserRole::SUPER_ADMIN->value);
        $context->clear();
    }
}
