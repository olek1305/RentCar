<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CurrencySetting;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    public function index(): Factory|Application|View
    {
        $currencies = CurrencySetting::all();
        $defaultCurrency = CurrencySetting::getDefaultCurrency();

        return view('admin.currencies.index', compact('currencies', 'defaultCurrency'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'currency_code' => 'required|string|size:3|unique:currency_settings,currency_code',
            'currency_symbol' => 'required|string|max:5',
            'currency_name' => 'required|string|max:100',
        ]);

        $validated['currency_code'] = strtoupper($validated['currency_code']);

        // If this is the first currency, make it default
        if (CurrencySetting::count() === 0) {
            $validated['is_default'] = true;
        }

        CurrencySetting::create($validated);

        return back()->with('success', __('messages.currency_created'));
    }

    public function update(Request $request, CurrencySetting $currency): RedirectResponse
    {
        $validated = $request->validate([
            'currency_code' => 'required|string|size:3|unique:currency_settings,currency_code,'.$currency->id,
            'currency_symbol' => 'required|string|max:5',
            'currency_name' => 'required|string|max:100',
        ]);

        $validated['currency_code'] = strtoupper($validated['currency_code']);

        $currency->update($validated);

        return back()->with('success', __('messages.currency_updated'));
    }

    public function destroy(CurrencySetting $currency): RedirectResponse
    {
        if ($currency->is_default) {
            return back()->with('error', __('messages.cannot_delete_default_currency'));
        }

        $currency->delete();

        return back()->with('success', __('messages.currency_deleted'));
    }

    public function setDefault(CurrencySetting $currency): RedirectResponse
    {
        // Reset all defaults
        CurrencySetting::query()->update(['is_default' => false]);

        // Set new default
        $currency->update(['is_default' => true]);

        // Update session
        session(['currency' => $currency]);

        return back()->with('success', __('messages.default_currency_updated'));
    }
}
