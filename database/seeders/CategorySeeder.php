<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $companies = DB::table('companies')
            ->whereIn('name', ['Demo Warehouse', 'Sample Trading'])
            ->pluck('id', 'name');

        foreach ([
            ['company' => 'Demo Warehouse', 'name' => 'General Goods'],
            ['company' => 'Sample Trading', 'name' => 'Office Supplies'],
        ] as $category) {
            DB::table('categories')->updateOrInsert(
                [
                    'company_id' => $companies[$category['company']],
                    'name' => $category['name'],
                ],
                [
                    'is_active' => true,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
