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
    /**
     * @return Factory|Application|View
     */
    public function index(): Factory|Application|View
    {
        return view('home');
    }

    /**
     * @return Factory|Application|View
     */
    public function condition(): Factory|Application|View
    {
        return view('condition');
    }

    /**
     * @return Factory|Application|View
     */
    public function contact(): Factory|Application|View
    {
        return view('contact');
    }
}
