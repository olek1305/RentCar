<?php

namespace App\Http\Controllers;

use App\Models\Car;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;

class CarController extends Controller
{
    public function create(): Factory|Application|View
    {
        return view('cars.create');
    }

    public function store(Request $request): Application|Redirector|RedirectResponse
    {
        $validated = $this->validateCarRequest($request);

        Car::create($validated);

        return redirect('/')->with('success', 'Car created successfully.');
    }

    public function show(Request $request, $id): Factory|Application|View
    {
        $lang = $request->query('lang', 'en');
        app()->setLocale($lang);

        $car = Car::findOrFail($id);

        return view('cars.show', compact('car'));
    }

    public function edit($id): Factory|Application|View
    {
        $car = Car::findOrFail($id);

        return view('cars.edit',  compact('car'));
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $validated = $this->validateCarRequest($request);

        $car = Car::findOrFail($id);
        $car->update($validated);

        return redirect()->route('cars.show', $car->id)->with('success', 'Car updated successfully.');
    }

    public function destroy($id): RedirectResponse
    {
        $car = Car::findOrFail($id);
        $car->delete();

        return redirect()->route('home')->with('success', 'Car deleted successfully.');
    }

    /**
     * @param Request $request
     * @return array
     */
    private function validateCarRequest(Request $request): array
    {
        $validated = $request->validate([
            'model' => 'required|string|max:255',
            'type' => 'required|string|in:Sedan,SUV,Hatchback,Coupe',
            'year' => 'required|integer|min:1900',
            'seats' => 'required|integer|min:1|max:9',
            'fuel_type' => 'required|string',
            'engine_capacity' => 'required|integer',
            'transmission' => 'required|string',
            'description' => 'nullable|string',
            'image' => 'nullable|url',
            'daily_price' => 'required|numeric|min:1'
        ]);

        $validated['rental_prices'] = json_encode([
            '1-2' => $validated['daily_price'],
            '3-6' => $validated['daily_price'] * 0.9,
            '7+' => $validated['daily_price'] * 0.8
        ]);

        return $validated;
    }
}
