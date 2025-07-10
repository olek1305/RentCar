<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;

class AdminDashboardController extends Controller
{
    public function index(): Factory|Application|View
    {
        return view('admin.index');
    }
}
