<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCurrencyRequest;
use App\Http\Requests\UpdateCurrencyRequest;
use App\Models\CurrencySetting;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class CurrencyController extends Controller
{
    public function index(): Factory|Application|View
    {
        $currencies = CurrencySetting::all();
        $defaultCurrency = CurrencySetting::getDefaultCurrency();

        return view('admin.currencies.index', compact('currencies', 'defaultCurrency'));
    }

    public function store(StoreCurrencyRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        if (CurrencySetting::count() === 0) {
            $validated['is_default'] = true;
        }

        CurrencySetting::create($validated);

        return back()->with('success', __('messages.currency_created'));
    }

    public function update(UpdateCurrencyRequest $request, CurrencySetting $currency): RedirectResponse
    {
        $currency->update($request->validated());

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
        DB::transaction(function () use ($currency) {
            CurrencySetting::query()->update(['is_default' => false]);
            $currency->is_default = true;
            $currency->save();
        });

        session(['currency' => $currency->fresh()]);

        return back()->with('success', __('messages.default_currency_updated'));
    }
}
