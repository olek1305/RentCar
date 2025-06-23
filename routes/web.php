<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HomeController;

Route::get('/', [HomeController::class, 'index']);

Route::get('/condition', function () {
    $lang = request()->query('lang', 'en');
    app()->setLocale($lang);

    return view('condition', ['lang' => $lang]);
});

Route::view('/contact', 'contact');
