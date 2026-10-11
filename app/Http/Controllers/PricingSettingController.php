<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PricingSettingController extends Controller
{
    public function edit(Request $request): View
    {
        return view('settings.index', [
            'company' => $request->user()->company,
            'activeTab' => 'pricing',
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'percent_markup' => ['required', 'numeric', 'min:0', 'max:9999.99'],
        ]);

        $request->user()->company->update($validated);

        return redirect()->route('settings.pricing.edit')->with('success', 'تم حفظ إعدادات التسعير.');
    }
}
