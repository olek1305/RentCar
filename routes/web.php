<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::get('/', [HomeController::class, 'index']);

Route::get('/cars/{id}', [CarController::class, 'show'])->name('cars.show');

Route::get('/condition', function () {
    $lang = request()->query('lang', 'en');
    app()->setLocale($lang);

    return view('condition', ['lang' => $lang]);
});

Route::get('/contact', function () {
    $lang = request()->query('lang', 'en');
    app()->setLocale($lang);

    return view('contact', ['lang' => $lang]);
});

Route::middleware(['auth'])->group(function () {
    Route::get('/cars/create', [CarController::class, 'create'])->name('cars.create');
    Route::post('/cars', [CarController::class, 'store'])->name('cars.store');
    Route::post('/cars/{id}', [CarController::class, 'update'])->name('cars.update');
    Route::delete('/cars/{id}', [CarController::class, 'destroy'])->name('cars.destroy');
});
