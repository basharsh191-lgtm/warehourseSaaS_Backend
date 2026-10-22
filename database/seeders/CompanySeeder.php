<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            [
                'name' => 'Demo Warehouse',
                'status' => 'active',
                'trial_ends_at' => null,
                'percent_markup' => 20.00,
                'phone' => '+1-555-0101',
                'email' => 'warehouse@example.test',
                'address' => '100 Market Street',
            ],
            [
                'name' => 'Sample Trading',
                'status' => 'trial',
                'trial_ends_at' => now()->addDays(14)->toDateString(),
                'percent_markup' => 15.00,
                'phone' => '+1-555-0102',
                'email' => 'trading@example.test',
                'address' => '200 Market Street',
            ],
        ] as $company) {
            DB::table('companies')->updateOrInsert(
                ['name' => $company['name']],
                [...$company, 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }
}
