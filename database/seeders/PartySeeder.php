<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PartySeeder extends Seeder
{
    public function run(): void
    {
        $companyId = DB::table('companies')
            ->where('name', 'Demo Warehouse')
            ->value('id');

        foreach ([
            [
                'name' => 'Demo Customer',
                'phone' => '+1-555-0201',
                'type' => 'customer',
            ],
            [
                'name' => 'Demo Supplier',
                'phone' => '+1-555-0202',
                'type' => 'supplier',
            ],
        ] as $party) {
            DB::table('parties')->updateOrInsert(
                [
                    'company_id' => $companyId,
                    'type' => $party['type'],
                    'phone' => $party['phone'],
                ],
                [
                    'name' => $party['name'],
                    'is_active' => true,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
