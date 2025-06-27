<?php

namespace App\Http\Controllers;

use App\Models\Car;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
    public function index(Request $request): Factory|Application|View
    {
        $lang = $request->query('lang', 'en');
        app()->setLocale($lang);

        $cars = Car::orderBy('created_at', 'desc')->paginate(12);

        $cars->getCollection()->transform(function ($car) {
            $car->main_image_url = Storage::url($car->main_image);

            if (is_string($car->rental_prices)) {
                $car->rental_prices = json_decode($car->rental_prices, true);
            }

            return $car;
        });
        return view('home', compact('cars', 'lang'));
    }

    public function condition(): Factory|Application|View
    {
        $lang = request()->query('lang', 'en');
        app()->setLocale($lang);

        return view('condition', ['lang' => $lang]);
    }

    public function contact(): Factory|Application|View
    {
        $lang = request()->query('lang', 'en');
        app()->setLocale($lang);

        return view('contact', ['lang' => $lang]);
    }
}
