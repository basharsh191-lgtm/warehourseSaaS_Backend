<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OperationSeeder extends Seeder
{
    public function run(): void
    {
        $companyId = DB::table('companies')
            ->where('name', 'Demo Warehouse')
            ->value('id');
        $items = DB::table('movement_items')
            ->join('movements', 'movements.id', '=', 'movement_items.movement_id')
            ->where('movements.company_id', $companyId)
            ->whereIn('movements.document_no', ['PO-DEMO-001', 'SO-DEMO-001'])
            ->select('movement_items.id', 'movement_items.movement_id', 'movement_items.product_id', 'movement_items.quantity', 'movement_items.price_unit')
            ->get();
        $movements = DB::table('movements')->whereIn('id', $items->pluck('movement_id'))->get()->keyBy('id');

        foreach ($items as $item) {
            $movement = $movements[$item->movement_id];
            $isPurchase = $movement->type === 'purchase';

            DB::table('operations')->updateOrInsert(
                ['movement_item_id' => $item->id],
                [
                    'company_id' => $companyId,
                    'movement_id' => $movement->id,
                    'product_id' => $item->product_id,
                    'party_id' => $movement->party_id,
                    'type' => $movement->type,
                    'date' => $movement->date,
                    'in_qty' => $isPurchase ? $item->quantity : 0,
                    'out_qty' => $isPurchase ? 0 : $item->quantity,
                    'price_unit' => $item->price_unit,
                    'value' => $item->quantity * $item->price_unit,
                    'value_net' => $item->quantity * $item->price_unit,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
