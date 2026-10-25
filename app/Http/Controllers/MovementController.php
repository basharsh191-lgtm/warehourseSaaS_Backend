<?php

namespace App\Http\Controllers;

use App\Models\Companie;
use App\Models\Movement;
use App\Models\MovementItems;
use App\Models\Operation;
use App\Models\Product;
use App\Services\MovementPdfService;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MovementController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->validatedFilters($request);
        $movements = $this->filteredMovements($request, $filters)
            ->paginate(15)
            ->withQueryString();
        $parties = $request->user()->company->parties()->orderBy('name')->get(['id', 'name', 'type']);

        return view('movements.index', [
            'movements' => $movements,
            'parties' => $parties,
            'currency' => $request->user()->company->currency,
        ]);
    }

    public function exportPdf(Request $request, MovementPdfService $pdfService): Response
    {
        $filters = $this->validatedFilters($request);
        $movements = $this->filteredMovements($request, $filters)->get();

        return $pdfService->report($movements, $request->user()->company->currency);
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
            'item_shape' => $product->item_shape,
        ])->values();
        $parties = $company->parties()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'type']);

        return view('movements.create', compact('company', 'productsPayload', 'parties'));
    }

    public function store(Request $request): RedirectResponse
    {
        $company = $request->user()->company;
        if (! $request->exists('currency')) {
            $request->merge(['currency' => $company->currency]);
        }

        if (! $request->exists('discount_value') && $request->exists('discount')) {
            $request->merge(['discount_value' => $request->input('discount')]);
        }

        $validated = $request->validate([
            'type' => ['required', Rule::in(['purchase', 'sale'])],
            'status' => ['sometimes', Rule::in(['confirmed', 'draft'])],
            'currency' => ['required', 'string', 'max:20', 'regex:/\S/'],
            'discount_type' => ['sometimes', Rule::in(['fixed', 'percentage'])],
            'discount_value' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'party_id' => [
                'nullable',
                Rule::exists('parties', 'id')->where('company_id', $company->id),
            ],
            'document_no' => ['nullable', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where('company_id', $company->id)->where('is_active', true),
            ],
            'items.*.quantity' => ['required', 'numeric', 'gt:0', 'max:999999999.999'],
            'items.*.price_unit' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'items.*.item_shape' => ['nullable', 'string', 'max:255'],
        ]);
        $validated['status'] = $validated['status'] ?? 'confirmed';
        $validated['discount_type'] = $validated['discount_type'] ?? 'fixed';
        $validated['currency'] = trim($validated['currency']);
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
            'item_shape' => $item['item_shape'] ?? null,
            'total_line' => round((float) $item['quantity'] * (float) $item['price_unit'], 2),
        ]);
        $subtotal = round($items->sum('total_line'), 2);
        $discountValue = round((float) $validated['discount_value'], 2);

        if ($items->contains(fn (array $item) => $item['total_line'] > 9999999999.99) || $subtotal > 9999999999.99) {
            throw ValidationException::withMessages(['items' => 'إجمالي أحد الأسطر أو الفاتورة يتجاوز الحد المسموح.']);
        }

        if ($validated['discount_type'] === 'percentage' && $discountValue > 100) {
            throw ValidationException::withMessages(['discount_value' => 'يجب ألا تتجاوز نسبة الخصم 100%.']);
        }

        $discountAmount = $validated['discount_type'] === 'percentage'
            ? round($subtotal * $discountValue / 100, 2)
            : $discountValue;

        if ($discountAmount > $subtotal) {
            throw ValidationException::withMessages(['discount_value' => 'يجب ألا يتجاوز الخصم إجمالي الفاتورة.']);
        }

        $movement = DB::transaction(function () use ($company, $validated, $items, $subtotal, $discountValue, $discountAmount) {
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

            if ($validated['status'] === 'confirmed' && $validated['type'] === 'sale') {
                $this->ensureSaleStock($company, $items, $products);
            }

            $number = $company->movements()->max('number') + 1;
            $movement = $company->movements()->create([
                'party_id' => $validated['party_id'] ?: null,
                'type' => $validated['type'],
                'number' => $number,
                'currency' => $validated['currency'],
                'document_no' => $validated['document_no'] ?: null,
                'date' => $validated['date'],
                'status' => $validated['status'],
                'discount_type' => $validated['discount_type'],
                'discount_value' => $discountValue,
                'discount_amount' => $discountAmount,
                'discount' => $discountAmount,
                'total' => round($subtotal - $discountAmount, 2),
            ]);

            foreach ($items as $item) {
                MovementItems::query()->create([
                    'movement_id' => $movement->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price_unit' => $item['price_unit'],
                    'item_shape' => $item['item_shape'],
                    'total_line' => $item['total_line'],
                ]);
            }

            if ($movement->status === 'confirmed') {
                $this->recordMovementOperations($company, $movement, $movement->items);
            }

            return $movement;
        });

        $message = $movement->status === 'draft'
            ? 'تم حفظ الفاتورة كمسودة دون تحديث المخزون.'
            : 'تم حفظ الفاتورة وتحديث المخزون.';

        return redirect()->route('movements.show', $movement)->with('success', $message);
    }

    public function show(Request $request, int $movement): View
    {
        $movement = $request->user()->company->movements()
            ->with(['company', 'party', 'items.product'])
            ->findOrFail($movement);

        return view('movements.show', compact('movement'));
    }

    public function exportInvoicePdf(Request $request, int $movement, MovementPdfService $pdfService): Response
    {
        $movement = $request->user()->company->movements()
            ->with(['company', 'party', 'items.product'])
            ->findOrFail($movement);

        return $pdfService->invoice($movement);
    }

    public function confirmDraft(Request $request, int $movement): RedirectResponse
    {
        $company = $request->user()->company;

        DB::transaction(function () use ($company, $movement) {
            Companie::query()->whereKey($company->id)->lockForUpdate()->firstOrFail();
            $draft = $company->movements()
                ->whereKey($movement)
                ->lockForUpdate()
                ->with('items')
                ->firstOrFail();

            if ($draft->status !== 'draft') {
                throw ValidationException::withMessages([
                    'status' => 'لا يمكن تأكيد هذه الفاتورة لأنها ليست مسودة.',
                ]);
            }

            if ($draft->party_id) {
                $partyType = $draft->type === 'purchase' ? 'supplier' : 'customer';
                $party = $company->parties()->find($draft->party_id);

                if (! $party || $party->type !== $partyType) {
                    throw ValidationException::withMessages([
                        'party_id' => 'الطرف المرتبط بالمسودة لم يعد متوافقًا مع نوع الفاتورة.',
                    ]);
                }
            }

            $items = $draft->items;
            $productIds = $items->pluck('product_id')->unique()->values();
            $products = Product::query()
                ->whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($products->count() !== $productIds->count()) {
                throw ValidationException::withMessages([
                    'items' => 'تتضمن المسودة مادة لم تعد متاحة ضمن الشركة.',
                ]);
            }

            if ($draft->type === 'sale') {
                $this->ensureSaleStock($company, $items, $products);
            }

            $this->recordMovementOperations($company, $draft, $items);
            $draft->update(['status' => 'confirmed']);
        });

        return redirect()->route('movements.show', $movement)->with('success', 'تم تأكيد المسودة وتحديث المخزون.');
    }

    private function validatedFilters(Request $request): array
    {
        $companyId = $request->user()->company_id;

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', Rule::in(['purchase', 'sale', 'purchase_return', 'sale_return'])],
            'status' => ['nullable', Rule::in(['confirmed', 'draft', 'cancelled'])],
            'party_id' => ['nullable', 'integer', Rule::exists('parties', 'id')->where('company_id', $companyId)],
            'date' => ['nullable', 'date_format:Y-m-d'],
            'from_date' => ['nullable', 'date_format:Y-m-d'],
            'to_date' => ['nullable', 'date_format:Y-m-d'],
        ]);

        if (
            filled($filters['from_date'] ?? null)
            && filled($filters['to_date'] ?? null)
            && $filters['to_date'] < $filters['from_date']
        ) {
            throw ValidationException::withMessages([
                'to_date' => 'يجب أن يكون تاريخ النهاية مساويًا لتاريخ البداية أو بعده.',
            ]);
        }

        return $filters;
    }

    private function filteredMovements(Request $request, array $filters): HasMany
    {
        return $request->user()->company->movements()
            ->with(['party', 'company'])
            ->when($filters['type'] ?? null, fn ($query, $type) => $query->where('type', $type))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['party_id'] ?? null, fn ($query, $partyId) => $query->where('party_id', $partyId))
            ->when($filters['date'] ?? null, fn ($query, $date) => $query->whereDate('date', $date))
            ->when($filters['from_date'] ?? null, fn ($query, $date) => $query->whereDate('date', '>=', $date))
            ->when($filters['to_date'] ?? null, fn ($query, $date) => $query->whereDate('date', '<=', $date))
            ->when(
                isset($filters['search']) && $filters['search'] !== '',
                function ($query) use ($filters) {
                    $search = trim($filters['search']);
                    $query->where(function ($query) use ($search) {
                        $query->where('number', 'like', "%{$search}%")
                            ->orWhere('document_no', 'like', "%{$search}%");
                    });
                }
            )
            ->latest('date')
            ->latest('number');
    }

    private function ensureSaleStock(Companie $company, Collection $items, Collection $products): void
    {
        $productIds = $items->pluck('product_id')->unique()->values();
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

    private function recordMovementOperations(Companie $company, Movement $movement, Collection $items): void
    {
        $subtotal = round($items->sum('total_line'), 2);
        $netRemaining = (float) $movement->total;
        $linesRemaining = $items->count();

        foreach ($items as $item) {
            $lineNet = --$linesRemaining === 0
                ? $netRemaining
                : ($subtotal > 0 ? round((float) $item->total_line * (float) $movement->total / $subtotal, 2) : 0);
            $netRemaining = round($netRemaining - $lineNet, 2);

            Operation::query()->create([
                'company_id' => $company->id,
                'movement_id' => $movement->id,
                'movement_item_id' => $item->id,
                'product_id' => $item->product_id,
                'party_id' => $movement->party_id,
                'type' => $movement->type,
                'date' => $movement->date,
                'in_qty' => $movement->type === 'purchase' ? $item->quantity : 0,
                'out_qty' => $movement->type === 'sale' ? $item->quantity : 0,
                'price_unit' => $item->price_unit,
                'value' => $item->total_line,
                'value_net' => $lineNet,
            ]);
        }
    }
}
