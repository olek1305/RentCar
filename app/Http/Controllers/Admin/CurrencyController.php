<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CurrencySetting;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;

class CurrencyController extends Controller
{
    public function index(): Factory|Application|View
    {
        $currencies = CurrencySetting::all();
        $defaultCurrency = CurrencySetting::getDefaultCurrency();

        return view('admin.currencies.index', compact('currencies', 'defaultCurrency'));
    }

    public function setDefault($id): RedirectResponse
    {
        // Reset all defaults
        CurrencySetting::query()->update(['is_default' => false]);

        // Set new default
        $currency = CurrencySetting::findOrFail($id);
        $currency->update(['is_default' => true]);

        // Update session
        session(['currency' => $currency]);

        return back()->with('success', 'Default currency updated');
    }
}
