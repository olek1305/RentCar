<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;

class HomeController extends Controller
{
    public function index(): Factory|Application|View
    {
        return view('home');
    }

    public function condition(): Factory|Application|View
    {
        return view('condition');
    }

    public function contact(): Factory|Application|View
    {
        return view('contact');
    }
}
