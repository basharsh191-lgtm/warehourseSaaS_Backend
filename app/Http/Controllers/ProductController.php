<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
use App\Models\Product;
use App\Services\ProductImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductController extends Controller
{
    public function index(): View
    {
        $products = Product::query()
            ->with('category')
            ->latest()
            ->paginate(15);

        return view('products.index', [
            'products' => $products,
            'currency' => auth()->user()->company->currency,
        ]);
    }

    public function create(): View
    {
        return view('products.form', [
            'product' => new Product,
            'categories' => Categorie::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedProduct($request);
        $request->user()->company->products()->create($validated);

        return redirect()->route('products.index')->with('success', 'تمت إضافة المادة بنجاح.');
    }

    public function edit(Product $product): View
    {
        return view('products.form', [
            'product' => $product,
            'categories' => Categorie::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $product->update($this->validatedProduct($request, $product));

        return redirect()->route('products.index')->with('success', 'تم تحديث المادة بنجاح.');
    }

    public function toggleStatus(Product $product): RedirectResponse
    {
        $product->update(['is_active' => ! $product->is_active]);

        return redirect()->route('products.index')->with('success', 'تم تحديث حالة المادة.');
    }

    public function import(Request $request, ProductImportService $importService): RedirectResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'max:10240'],
        ]);
        $extension = strtolower($validated['file']->getClientOriginalExtension());

        if (! in_array($extension, ['csv', 'ods', 'xlsx'], true)) {
            throw ValidationException::withMessages([
                'file' => 'يُسمح بملفات CSV أو ODS أو XLSX فقط.',
            ]);
        }

        $result = $importService->import($validated['file'], $request->user()->company);
        $message = "تم استيراد {$result['imported']} مادة وتخطي {$result['skipped']} صف.";

        return redirect()
            ->route('products.index')
            ->with('success', $message)
            ->with('import_errors', $result['errors']);
    }

    public function exportExcel(Request $request, FastExcel $fastExcel): StreamedResponse
    {
        $products = Product::query()
            ->with('category')
            ->withSum('operations as total_in_qty', 'in_qty')
            ->withSum('operations as total_out_qty', 'out_qty')
            ->orderBy('name')
            ->lazy(500);

        return $fastExcel
            ->escapeFormulas()
            ->data($products)
            ->download('products.xlsx', fn (Product $product) => [
                'الاسم' => $product->name,
                'الباركود' => (string) ($product->barcode ?? ''),
                'الفئة' => $product->category?->name,
                'الوحدة' => $product->unit,
                'سعر البيع' => $product->price_sale_custom,
                'الكمية الأولية' => 0,
                'المخزون الحالي' => (float) ($product->total_in_qty ?? 0) - (float) ($product->total_out_qty ?? 0),
            ]);
    }

    private function validatedProduct(Request $request, ?Product $product = null): array
    {
        $companyId = $request->user()->company_id;
        $barcodeRule = Rule::unique('products', 'barcode')->where('company_id', $companyId);

        if ($product) {
            $barcodeRule->ignore($product->id);
        }

        return $request->validate([
            'barcode' => [
                'nullable',
                'string',
                'max:255',
                $barcodeRule,
            ],
            'name' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'string', 'max:100'],
            'location' => ['nullable', 'string', 'max:255'],
            'item_shape' => ['nullable', 'string', 'max:255'],
            'price_sale_custom' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'stock_min' => ['nullable', 'numeric', 'min:0', 'max:999999999.999'],
            'category_id' => [
                'nullable',
                Rule::exists('categories', 'id')->where('company_id', $companyId),
            ],
        ]);
    }
}
