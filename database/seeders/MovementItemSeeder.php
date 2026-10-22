<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MovementItemSeeder extends Seeder
{
    public function run(): void
    {
        $movements = DB::table('movements')
            ->where('company_id', DB::table('companies')->where('name', 'Demo Warehouse')->value('id'))
            ->whereIn('document_no', ['PO-DEMO-001', 'SO-DEMO-001'])
            ->pluck('id', 'document_no');
        $productId = DB::table('products')
            ->where('barcode', 'DEMO-0001')
            ->value('id');

        foreach ([
            ['document_no' => 'PO-DEMO-001', 'quantity' => 10.000, 'price_unit' => 10.00],
            ['document_no' => 'SO-DEMO-001', 'quantity' => 2.000, 'price_unit' => 12.50],
        ] as $item) {
            DB::table('movement_items')->updateOrInsert(
                [
                    'movement_id' => $movements[$item['document_no']],
                    'product_id' => $productId,
                ],
                [
                    'quantity' => $item['quantity'],
                    'price_unit' => $item['price_unit'],
                    'total_line' => $item['quantity'] * $item['price_unit'],
                    'item_shape' => 'box',
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
