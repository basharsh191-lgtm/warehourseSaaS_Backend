<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        $products = Product::query()
            ->with('category')
            ->latest()
            ->paginate(15);

        return view('products.index', compact('products'));
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
