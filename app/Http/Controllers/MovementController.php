<?php

namespace App\Http\Controllers;

use App\Models\Movement;
use App\Models\MovementItems;
use App\Models\Operation;
use App\Models\Product;
use App\Models\Companie;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MovementController extends Controller
{
    public function index(Request $request): View
    {
        $movements = $request->user()->company->movements()
            ->with('party')
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->input('type')))
            ->when($request->filled('date'), fn ($query) => $query->whereDate('date', $request->input('date')))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->string('search')->toString());
                $query->where(function ($query) use ($search) {
                    $query->where('number', 'like', "%{$search}%")
                        ->orWhere('document_no', 'like', "%{$search}%");
                });
            })
            ->latest('date')
            ->latest('number')
            ->paginate(15)
            ->withQueryString();

        return view('movements.index', compact('movements'));
    }

    public function create(Request $request): View
    {
        $company = $request->user()->company;
        $products = Product::query()->where('is_active', true)->orderBy('name')->get();
        $stocks = Operation::query()
            ->select('product_id', DB::raw('SUM(in_qty) - SUM(out_qty) as stock'))
            ->where('company_id', $company->id)
            ->whereIn('product_id', $products->modelKeys())
            ->groupBy('product_id')
            ->pluck('stock', 'product_id');
        $purchasePrices = Operation::query()
            ->where('company_id', $company->id)
            ->where('type', 'purchase')
            ->whereIn('product_id', $products->modelKeys())
            ->latest('date')
            ->latest('id')
            ->get(['product_id', 'price_unit'])
            ->unique('product_id')
            ->pluck('price_unit', 'product_id');

        $productsPayload = $products->map(fn (Product $product) => [
            'id' => $product->id,
            'name' => $product->name,
            'barcode' => $product->barcode,
            'stock' => (float) ($stocks[$product->id] ?? 0),
            'purchase_price' => (float) ($purchasePrices[$product->id] ?? 0),
            'sale_price' => $product->price_sale_custom === null ? null : (float) $product->price_sale_custom,
        ])->values();
        $parties = $company->parties()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'type']);

        return view('movements.create', compact('company', 'productsPayload', 'parties'));
    }

    public function store(Request $request): RedirectResponse
    {
        $company = $request->user()->company;
        $validated = $request->validate([
            'type' => ['required', Rule::in(['purchase', 'sale'])],
            'party_id' => [
                'nullable',
                Rule::exists('parties', 'id')->where('company_id', $company->id),
            ],
            'document_no' => ['nullable', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'discount' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where('company_id', $company->id)->where('is_active', true),
            ],
            'items.*.quantity' => ['required', 'numeric', 'gt:0', 'max:999999999.999'],
            'items.*.price_unit' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
        ]);
        $validated['party_id'] = $validated['party_id'] ?? null;
        $validated['document_no'] = $validated['document_no'] ?? null;

        if ($validated['party_id']) {
            $partyType = $validated['type'] === 'purchase' ? 'supplier' : 'customer';
            $party = $company->parties()->where('is_active', true)->find($validated['party_id']);
            if (! $party || $party->type !== $partyType) {
                throw ValidationException::withMessages(['party_id' => 'الطرف المحدد لا يتوافق مع نوع الفاتورة.']);
            }
        }

        $items = collect($validated['items'])->map(fn (array $item) => [
            'product_id' => (int) $item['product_id'],
            'quantity' => (float) $item['quantity'],
            'price_unit' => (float) $item['price_unit'],
            'total_line' => round((float) $item['quantity'] * (float) $item['price_unit'], 2),
        ]);
        $subtotal = round($items->sum('total_line'), 2);
        $discount = round((float) $validated['discount'], 2);

        if ($items->contains(fn (array $item) => $item['total_line'] > 9999999999.99) || $subtotal > 9999999999.99) {
            throw ValidationException::withMessages(['items' => 'إجمالي أحد الأسطر أو الفاتورة يتجاوز الحد المسموح.']);
        }

        if ($discount > $subtotal) {
            throw ValidationException::withMessages(['discount' => 'يجب ألا يتجاوز الخصم إجمالي الفاتورة.']);
        }

        $movement = DB::transaction(function () use ($company, $validated, $items, $subtotal, $discount) {
            Companie::query()->whereKey($company->id)->lockForUpdate()->firstOrFail();
            $productIds = $items->pluck('product_id')->unique()->values();
            $products = Product::query()
                ->whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($products->count() !== $productIds->count()) {
                throw ValidationException::withMessages(['items' => 'تتضمن الفاتورة مادة غير متاحة.']);
            }

            if ($validated['type'] === 'sale') {
                $stocks = Operation::query()
                    ->select('product_id', DB::raw('SUM(in_qty) - SUM(out_qty) as stock'))
                    ->where('company_id', $company->id)
                    ->whereIn('product_id', $productIds)
                    ->groupBy('product_id')
                    ->pluck('stock', 'product_id');
                $requested = $items->groupBy('product_id')->map(fn ($lines) => $lines->sum('quantity'));

                foreach ($requested as $productId => $quantity) {
                    if ($quantity > (float) ($stocks[$productId] ?? 0)) {
                        throw ValidationException::withMessages([
                            'items' => "المخزون المتاح من {$products[$productId]->name} غير كافٍ.",
                        ]);
                    }
                }
            }

            $number = $company->movements()->max('number') + 1;
            $movement = $company->movements()->create([
                'party_id' => $validated['party_id'] ?: null,
                'type' => $validated['type'],
                'number' => $number,
                'document_no' => $validated['document_no'] ?: null,
                'date' => $validated['date'],
                'status' => 'confirmed',
                'discount' => $discount,
                'total' => round($subtotal - $discount, 2),
            ]);

            $netRemaining = round($subtotal - $discount, 2);
            $linesRemaining = $items->count();

            foreach ($items as $item) {
                $movementItem = MovementItems::query()->create([
                    'movement_id' => $movement->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price_unit' => $item['price_unit'],
                    'total_line' => $item['total_line'],
                ]);
                $lineNet = --$linesRemaining === 0
                    ? $netRemaining
                    : ($subtotal > 0 ? round($item['total_line'] * ($subtotal - $discount) / $subtotal, 2) : 0);
                $netRemaining = round($netRemaining - $lineNet, 2);

                Operation::query()->create([
                    'company_id' => $company->id,
                    'movement_id' => $movement->id,
                    'movement_item_id' => $movementItem->id,
                    'product_id' => $item['product_id'],
                    'party_id' => $validated['party_id'] ?: null,
                    'type' => $validated['type'],
                    'date' => $validated['date'],
                    'in_qty' => $validated['type'] === 'purchase' ? $item['quantity'] : 0,
                    'out_qty' => $validated['type'] === 'sale' ? $item['quantity'] : 0,
                    'price_unit' => $item['price_unit'],
                    'value' => $item['total_line'],
                    'value_net' => $lineNet,
                ]);
            }

            return $movement;
        });

        return redirect()->route('movements.show', $movement)->with('success', 'تم حفظ الفاتورة وتحديث المخزون.');
    }

    public function show(Request $request, int $movement): View
    {
        $movement = $request->user()->company->movements()
            ->with(['party', 'items.product'])
            ->findOrFail($movement);

        return view('movements.show', compact('movement'));
    }
}
