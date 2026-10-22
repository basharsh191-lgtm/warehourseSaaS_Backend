<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Companie;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CompanyController extends Controller
{
    public function index(): View
    {
        Companie::suspendExpiredTrials();

        $companies = Companie::query()
            ->withCount('users')
            ->latest()
            ->paginate(15);

        return view('admin.companies.index', compact('companies'));
    }

    public function edit(Companie $company): View
    {
        Companie::suspendExpiredTrials();
        $company->refresh();

        return view('admin.companies.edit', compact('company'));
    }

    public function update(Request $request, Companie $company): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['trial', 'active', 'suspended'])],
            'trial_ends_at' => ['nullable', 'date_format:Y-m-d', 'required_if:status,trial'],
            'percent_markup' => ['required', 'numeric', 'min:0', 'max:9999.99'],
        ]);

        if ($validated['trial_ends_at'] && $validated['trial_ends_at'] < today()->toDateString()) {
            $validated['status'] = 'suspended';
        } elseif ($validated['trial_ends_at'] && $validated['status'] !== 'suspended') {
            $validated['status'] = $validated['status'] === 'active' ? 'active' : 'trial';
        }

        $company->update($validated);

        return redirect()
            ->route('admin.companies.edit', $company)
            ->with('success', 'تم تحديث بيانات الشركة بنجاح.');
    }

    public function extendTrial(Request $request, Companie $company): RedirectResponse
    {
        $validated = $request->validate([
            'days' => ['required', 'integer', 'min:1', 'max:3650'],
        ]);

        $baseDate = $company->trial_ends_at?->isAfter(today())
            ? $company->trial_ends_at->copy()
            : today();

        $company->update([
            'trial_ends_at' => $baseDate->addDays((int) $validated['days']),
            'status' => $company->status === 'active' ? 'active' : 'trial',
        ]);

        return redirect()
            ->route('admin.companies.edit', $company)
            ->with('success', 'تم تمديد الفترة التجريبية وتحديث حالة الشركة.');
    }
}
