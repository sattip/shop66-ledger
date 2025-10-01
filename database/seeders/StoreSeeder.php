<?php

namespace Database\Seeders;

use App\Models\Store;
use Illuminate\Database\Seeder;

class StoreSeeder extends Seeder
{
    public function run(): void
    {
        Store::firstOrCreate(
            ['code' => 'default-store'],
            [
                'name' => 'Default Store',
                'currency_code' => 'USD',
                'timezone' => 'UTC',
            ]
        );
    }
}


