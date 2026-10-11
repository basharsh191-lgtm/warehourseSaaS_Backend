<?php

namespace App\Http\Controllers;

use App\Http\Requests\PartieRequest;
use App\Models\Partie;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PartieController extends Controller
{
    public function index(Request $request): View
    {
        $parties = $request->user()->company->parties()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->string('search')->toString());
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->input('type')))
            ->when($request->filled('status'), fn ($query) => $query->where('is_active', $request->input('status') === 'active'))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('parties.index', compact('parties'));
    }

    public function store(PartieRequest $request): RedirectResponse
    {
        $request->user()->company->parties()->create($request->validated());

        return redirect()->route('parties.index')->with('success', 'تمت إضافة الطرف بنجاح.');
    }

    public function update(PartieRequest $request, int $party): RedirectResponse
    {
        $party = $request->user()->company->parties()->findOrFail($party);
        $party->update($request->validated());

        return redirect()->route('parties.index')->with('success', 'تم تحديث بيانات الطرف.');
    }

    public function toggleStatus(Request $request, int $party): RedirectResponse
    {
        $party = $request->user()->company->parties()->findOrFail($party);
        $party->update(['is_active' => ! $party->is_active]);

        return redirect()->route('parties.index')->with('success', 'تم تحديث حالة الطرف.');
    }
}
