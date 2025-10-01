<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Store;
use App\Models\User;
use App\Enums\UserRole;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Ensure we have at least one default store
        $this->call(StoreSeeder::class);
        $storeId = Store::first()->id;

        // 2. Ensure we have a default user
        $user = User::first();
        if (!$user) {
            $user = User::factory()->create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
            ]);
        }

        // 3. Ensure ADMIN role exists
        $role = \Spatie\Permission\Models\Role::firstOrCreate(
            ['name' => UserRole::ADMIN->value],
            ['guard_name' => 'web']
        );

        // 4. Attach role to user with store_id (no null allowed)
        DB::table('model_has_roles')->updateOrInsert(
            [
                'model_id'   => $user->id,
                'model_type' => User::class,
                'role_id'    => $role->id,
            ],
            [
                'store_id'   => $storeId,
            ]
        );

        // 5. Fix any existing rows with null store_id
        DB::table('model_has_roles')
            ->whereNull('store_id')
            ->update(['store_id' => $storeId]);
    }
}
