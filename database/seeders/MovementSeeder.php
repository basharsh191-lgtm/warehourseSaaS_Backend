<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MovementSeeder extends Seeder
{
    public function run(): void
    {
        $companyId = DB::table('companies')
            ->where('name', 'Demo Warehouse')
            ->value('id');
        $parties = DB::table('parties')
            ->where('company_id', $companyId)
            ->pluck('id', 'type');

        foreach ([
            [
                'number' => 1,
                'type' => 'purchase',
                'party_id' => $parties['supplier'],
                'document_no' => 'PO-DEMO-001',
                'total' => 100.00,
            ],
            [
                'number' => 2,
                'type' => 'sale',
                'party_id' => $parties['customer'],
                'document_no' => 'SO-DEMO-001',
                'total' => 25.00,
            ],
        ] as $movement) {
            DB::table('movements')->updateOrInsert(
                [
                    'company_id' => $companyId,
                    'document_no' => $movement['document_no'],
                ],
                [
                    'number' => $movement['number'],
                    'type' => $movement['type'],
                    'party_id' => $movement['party_id'],
                    'document_no' => $movement['document_no'],
                    'no_document' => $movement['document_no'],
                    'date' => now()->toDateString(),
                    'status' => 'confirmed',
                    'discount' => 0,
                    'total' => $movement['total'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
