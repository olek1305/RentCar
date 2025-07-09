<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/cars', [CarController::class, 'index'])->name('cars.index');
Route::get('/condition', [HomeController::class, 'condition'])->name('condition');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth'])->group(function () {
    Route::get('/cars/create', [CarController::class, 'create'])->name('cars.create');
    Route::get('/car/edit/{car}', [CarController::class, 'edit'])->name('cars.edit');
    Route::post('/cars', [CarController::class, 'store'])->name('cars.store');
    Route::put('/cars/{car}', [CarController::class, 'update'])->name('cars.update');
    Route::delete('/cars/{car}', [CarController::class, 'destroy'])->name('cars.destroy');

    Route::patch('/cars/{car}/toggle-visibility', [CarController::class, 'toggleVisibility'])
        ->middleware('auth')
        ->name('cars.toggle-visibility');

    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
});

Route::get('/cars/{car}', [CarController::class, 'show'])->name('cars.show');

Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');

Route::prefix('admin')->middleware('auth')->group(function () {
    Route::get('/orders', [\App\Http\Controllers\Admin\OrderController::class, 'index'])
        ->name('admin.orders.index');
    Route::get('/orders/{order}', [\App\Http\Controllers\Admin\OrderController::class, 'show'])
        ->name('admin.orders.show');
    Route::patch('/orders/{order}/status', [\App\Http\Controllers\Admin\OrderController::class, 'updateStatus'])
        ->name('admin.orders.update-status');
});


Route::get('/test-success', function () {
    return redirect()->route('home')->with('success', __('messages.order_created'));
});
