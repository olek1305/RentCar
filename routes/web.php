<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/cars', [CarController::class, 'index'])->name('cars');
Route::get('/condition', [HomeController::class, 'condition'])->name('condition');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth'])->group(function () {
    Route::get('/cars/create', [CarController::class, 'create'])->name('cars.create');
    Route::post('/cars', [CarController::class, 'store'])->name('cars.store');
    Route::put('/cars/{id}', [CarController::class, 'update'])->name('cars.update');
    Route::delete('/cars/{id}', [CarController::class, 'destroy'])->name('cars.destroy');

    Route::patch('/cars/{car}/toggle-visibility', [CarController::class, 'toggleVisibility'])
        ->middleware('auth')
        ->name('cars.toggle-visibility');

    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
});
Route::get('/cars/{id}', [CarController::class, 'show'])->name('cars.show');

Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
