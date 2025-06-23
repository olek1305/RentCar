<?php

namespace App\Http\Controllers;

use App\Models\Car;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $lang = $request->query('lang', 'en');
        app()->setLocale($lang);

        $cars = Car::limit(10)->get();

        return view('home', compact('cars', 'lang'));
    }
}
