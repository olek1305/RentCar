<?php

namespace App\Http\Controllers;

use App\Models\Car;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

class CarController extends Controller
{
    public function create(): Factory|Application|View
    {
        return view('cars.create');
    }

    public function show(Request $request, $id): Factory|Application|View
    {
        $lang = $request->query('lang', 'en');
        app()->setLocale($lang);

        $car = Car::findOrFail($id);
        return view('cars.show', compact('car'));
    }

    public function edit(): Factory|Application|View
    {
        return view('cars.edit');
    }

    public function update()
    {
        return;
    }

    public function destroy()
    {
        return;
    }
}
