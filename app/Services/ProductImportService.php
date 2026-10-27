<?php

namespace App\Services;

use App\Models\Categorie;
use App\Models\Companie;
use App\Models\MovementItems;
use App\Models\Operation;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Rap2hpoutre\FastExcel\FastExcel;

class ProductImportService
{
    private const BATCH_SIZE = 500;

    public function import(UploadedFile $file, Companie $company): array
    {
        $requiredHeaders = ['name', 'barcode', 'category', 'unit', 'price', 'initial_quantity'];
        $errors = [];
        $pendingRows = [];
        $imported = 0;
        $skipped = 0;
        $rowNumber = 1;
        $hasHeaders = false;

        foreach ((new FastExcel)->importLazy($file) as $row) {
            $rowNumber++;

            if (! is_array($row)) {
                continue;
            }

            $row = collect($row)->mapWithKeys(fn ($value, $key) => [
                mb_strtolower(trim((string) $key)) => $value,
            ])->all();

            if (! $hasHeaders) {
                $missingHeaders = array_diff($requiredHeaders, array_keys($row));
                if ($missingHeaders !== []) {
                    throw ValidationException::withMessages([
                        'file' => 'يجب أن يحتوي الملف على الأعمدة: '.implode(', ', $requiredHeaders).'.',
                    ]);
                }
                $hasHeaders = true;
            }

            if (collect($row)->every(fn ($value) => $value === null || (is_string($value) && trim($value) === ''))) {
                continue;
            }

            $row['name'] = isset($row['name']) ? trim((string) $row['name']) : null;
            $row['unit'] = isset($row['unit']) ? trim((string) $row['unit']) : null;
            $row['barcode'] = isset($row['barcode']) && trim((string) $row['barcode']) !== ''
                ? trim((string) $row['barcode'])
                : null;
            $row['category'] = isset($row['category']) && trim((string) $row['category']) !== ''
                ? trim((string) $row['category'])
                : null;
            $row['price'] = isset($row['price']) && trim((string) $row['price']) !== ''
                ? $row['price']
                : null;
            $row['initial_quantity'] = isset($row['initial_quantity']) && trim((string) $row['initial_quantity']) !== ''
                ? $row['initial_quantity']
                : null;

            $validator = Validator::make($row, [
                'name' => ['required', 'string', 'max:255'],
                'barcode' => ['nullable', 'string', 'max:255'],
                'category' => ['nullable', 'string', 'max:255'],
                'unit' => ['required', 'string', 'max:100'],
                'price' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
                'initial_quantity' => ['nullable', 'numeric', 'min:0', 'max:999999999.999'],
            ]);

            if ($validator->fails()) {
                $skipped++;
                $this->appendErrors($errors, $rowNumber, $validator->errors()->all());

                continue;
            }

            $pendingRows[] = [
                'row_number' => $rowNumber,
                'name' => $row['name'],
                'barcode' => $row['barcode'],
                'category' => $row['category'],
                'unit' => $row['unit'],
                'price' => $row['price'] === null || $row['price'] === '' ? null : (float) $row['price'],
                'initial_quantity' => $row['initial_quantity'] === null || $row['initial_quantity'] === ''
                    ? 0
                    : (float) $row['initial_quantity'],
            ];

            if (count($pendingRows) >= self::BATCH_SIZE) {
                $result = $this->importBatch($pendingRows, $company, $errors);
                $imported += $result['imported'];
                $skipped += $result['skipped'];
                $pendingRows = [];
            }
        }

        if (! $hasHeaders) {
            throw ValidationException::withMessages(['file' => 'الملف لا يحتوي على صف عناوين وبيانات.']);
        }

        if ($pendingRows !== []) {
            $result = $this->importBatch($pendingRows, $company, $errors);
            $imported += $result['imported'];
            $skipped += $result['skipped'];
        }

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }

    private function importBatch(array $rows, Companie $company, array &$errors): array
    {
        return DB::transaction(function () use ($rows, $company, &$errors): array {
            $lockedCompany = Companie::query()->whereKey($company->id)->lockForUpdate()->firstOrFail();
            $barcodes = collect($rows)
                ->pluck('barcode')
                ->filter(fn ($barcode) => $barcode !== null && $barcode !== '')
                ->unique();
            $existingBarcodes = $barcodes->isEmpty()
                ? collect()
                : Product::withoutGlobalScopes()
                    ->where('company_id', $company->id)
                    ->whereIn('barcode', $barcodes)
                    ->pluck('barcode')
                    ->map(fn ($barcode) => mb_strtolower($barcode))
                    ->flip();
            $categoryNames = collect($rows)->pluck('category')->filter()->unique();
            $categories = $categoryNames->isEmpty()
                ? collect()
                : Categorie::withoutGlobalScopes()
                    ->where('company_id', $company->id)
                    ->whereIn('name', $categoryNames)
                    ->pluck('id', 'name');
            $validRows = [];

            foreach ($rows as $row) {
                if ($row['barcode'] !== null && $existingBarcodes->has(mb_strtolower($row['barcode']))) {
                    $this->appendErrors($errors, $row['row_number'], ['الباركود مستخدم مسبقًا في شركتك أو مكرر داخل الملف.']);

                    continue;
                }

                if ($row['category'] !== null && ! $categories->has($row['category'])) {
                    $this->appendErrors($errors, $row['row_number'], ['الفئة غير موجودة ضمن شركتك.']);

                    continue;
                }

                $validRows[] = $row;
                if ($row['barcode'] !== null) {
                    $existingBarcodes->put(mb_strtolower($row['barcode']), true);
                }
            }

            $products = [];
            foreach ($validRows as $row) {
                $products[] = [
                    'product' => $lockedCompany->products()->create([
                        'category_id' => $row['category'] === null ? null : $categories[$row['category']],
                        'barcode' => $row['barcode'],
                        'name' => $row['name'],
                        'unit' => $row['unit'],
                        'price_sale_custom' => $row['price'],
                        'is_active' => true,
                    ]),
                    'row' => $row,
                ];
            }

            $openingProducts = collect($products)
                ->filter(fn (array $entry) => $entry['row']['initial_quantity'] > 0)
                ->values();

            if ($openingProducts->isNotEmpty()) {
                $movement = $lockedCompany->movements()->create([
                    'type' => 'purchase',
                    'number' => ((int) $lockedCompany->movements()->max('number')) + 1,
                    'document_no' => 'رصيد افتتاحي للاستيراد',
                    'date' => today(),
                    'status' => 'confirmed',
                    'currency' => $lockedCompany->currency,
                    'discount_type' => 'fixed',
                    'discount_value' => 0,
                    'discount_amount' => 0,
                    'discount' => 0,
                    'total' => 0,
                ]);

                foreach ($openingProducts as $entry) {
                    $product = $entry['product'];
                    $row = $entry['row'];
                    $item = MovementItems::query()->create([
                        'movement_id' => $movement->id,
                        'product_id' => $product->id,
                        'quantity' => $row['initial_quantity'],
                        'price_unit' => 0,
                        'total_line' => 0,
                    ]);

                    Operation::query()->create([
                        'company_id' => $lockedCompany->id,
                        'movement_id' => $movement->id,
                        'movement_item_id' => $item->id,
                        'product_id' => $product->id,
                        'type' => 'purchase',
                        'date' => today(),
                        'in_qty' => $row['initial_quantity'],
                        'out_qty' => 0,
                        'price_unit' => 0,
                        'value' => 0,
                        'value_net' => 0,
                    ]);
                }
            }

            return [
                'imported' => count($validRows),
                'skipped' => count($rows) - count($validRows),
            ];
        });
    }

    private function appendErrors(array &$errors, int $row, array $messages): void
    {
        if (count($errors) >= 50) {
            return;
        }

        $errors[] = [
            'row' => $row,
            'messages' => $messages,
        ];
    }
}
