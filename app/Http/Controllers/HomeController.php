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
    public function index(): Factory|Application|View
    {
        return view('home');
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
