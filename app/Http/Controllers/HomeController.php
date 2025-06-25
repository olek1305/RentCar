<?php

namespace App\Http\Controllers;

use App\Models\Car;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request): Factory|Application|View
    {
        $lang = $request->query('lang', 'en');
        app()->setLocale($lang);

        $cars = Car::limit(9)->get();

        return view('home', compact('cars', 'lang'));
    }

    public function show(Request $request, $id): Factory|Application|View
    {
        $lang = $request->query('lang', 'en');
        app()->setLocale($lang);

        $car = Car::findOrFail($id);
        return view('cars.show', compact('car'));
    }
}
