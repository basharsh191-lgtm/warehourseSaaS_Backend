<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Categorie::query()->latest()->paginate(15);

        return view('categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('categories.form', ['category' => new Categorie]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->where('company_id', $request->user()->company_id),
            ],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);

        $request->user()->company->categories()->create($validated);

        return redirect()->route('categories.index')->with('success', 'تمت إضافة الفئة بنجاح.');
    }

    public function edit(Categorie $category): View
    {
        return view('categories.form', compact('category'));
    }

    public function update(Request $request, Categorie $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')
                    ->where('company_id', $request->user()->company_id)
                    ->ignore($category->id),
            ],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);

        $category->update($validated);

        return redirect()->route('categories.index')->with('success', 'تم تحديث الفئة بنجاح.');
    }

    public function toggleStatus(Categorie $category): RedirectResponse
    {
        $category->update(['is_active' => ! $category->is_active]);

        return redirect()->route('categories.index')->with('success', 'تم تحديث حالة الفئة.');
    }
}
