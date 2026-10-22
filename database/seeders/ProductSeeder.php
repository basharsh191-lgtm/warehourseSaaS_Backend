<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $companies = DB::table('companies')
            ->whereIn('name', ['Demo Warehouse', 'Sample Trading'])
            ->pluck('id', 'name');
        $categories = DB::table('categories')
            ->whereIn('name', ['General Goods', 'Office Supplies'])
            ->get()
            ->keyBy('name');

        foreach ([
            [
                'company' => 'Demo Warehouse',
                'category' => 'General Goods',
                'barcode' => 'DEMO-0001',
                'name' => 'Packing Box',
                'unit' => 'piece',
                'location' => 'A-01',
                'item_shape' => 'box',
                'price_sale_custom' => 12.50,
                'stock_min' => 5.000,
            ],
            [
                'company' => 'Sample Trading',
                'category' => 'Office Supplies',
                'barcode' => 'DEMO-0002',
                'name' => 'Notebook',
                'unit' => 'piece',
                'location' => 'B-01',
                'item_shape' => 'unit',
                'price_sale_custom' => 4.00,
                'stock_min' => 10.000,
            ],
        ] as $product) {
            DB::table('products')->updateOrInsert(
                ['barcode' => $product['barcode']],
                [
                    'company_id' => $companies[$product['company']],
                    'category_id' => $categories[$product['category']]->id,
                    'name' => $product['name'],
                    'unit' => $product['unit'],
                    'location' => $product['location'],
                    'item_shape' => $product['item_shape'],
                    'price_sale_custom' => $product['price_sale_custom'],
                    'stock_min' => $product['stock_min'],
                    'is_active' => true,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
