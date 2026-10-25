<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CompanySettingsController extends Controller
{
    public function index(Request $request): View
    {
        return view('settings.index', [
            'company' => $request->user()->company,
            'activeTab' => $request->query('tab') === 'company' ? 'company' : 'pricing',
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'currency' => ['required', Rule::in(['USD', 'EUR', 'SYP', 'SAR', 'AED'])],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $company = $request->user()->company;
        $previousLogo = $company->logo_path;

        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('company-logos', 'public');

            if (! is_string($logoPath) || $logoPath === '') {
                throw ValidationException::withMessages([
                    'logo' => 'تعذر حفظ الشعار. يرجى المحاولة مرة أخرى.',
                ]);
            }

            $validated['logo_path'] = $logoPath;
        }

        unset($validated['logo']);
        $company->update($validated);

        if (isset($logoPath) && $previousLogo && $previousLogo !== $logoPath) {
            Storage::disk('public')->delete($previousLogo);
        }

        return redirect()->route('settings.index', ['tab' => 'company'])
            ->with('success', 'تم حفظ بيانات الشركة.');
    }
}
