<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\Admin\OrderController as OrderAdminController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/cars', [CarController::class, 'index'])->name('cars.index');
Route::get('/condition', [HomeController::class, 'condition'])->name('condition');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');

Route::get('/admin/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/admin/login', [AuthController::class, 'login']);

Route::middleware(['auth'])->group(function () {
    Route::get('/cars/create', [CarController::class, 'create'])->name('cars.create');
    Route::get('/car/edit/{car}', [CarController::class, 'edit'])->name('cars.edit');
    Route::post('/cars', [CarController::class, 'store'])->name('cars.store');
    Route::put('/cars/{car}', [CarController::class, 'update'])->name('cars.update');
    Route::delete('/cars/{car}', [CarController::class, 'destroy'])->name('cars.destroy');

    Route::patch('/cars/{car}/toggle-visibility', [CarController::class, 'toggleVisibility'])
        ->middleware('auth')
        ->name('cars.toggle-visibility');
});

Route::get('/cars/{car}', [CarController::class, 'show'])->name('cars.show');

Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');

Route::prefix('admin')->middleware('auth')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('admin.index');
    Route::get('/orders', [OrderAdminController::class, 'index'])->name('admin.orders.index');
    Route::get('/orders/{order}', [OrderAdminController::class, 'show'])->name('admin.orders.show');
    Route::patch('/orders/{order}/status', [OrderAdminController::class, 'updateStatus'])->name('admin.orders.update-status');

    Route::post('/logout', [AuthController::class, 'logout'])->name('admin.logout');
});

// verify email/sms
Route::get('/orders/{order}/verification', [OrderController::class, 'verification'])->name('orders.verification');
Route::get('/orders/verify-email/{token}', [OrderController::class, 'verifyEmail'])->name('orders.verify-email');
Route::post('/orders/{order}/verify-sms', [OrderController::class, 'verifySms'])->name('orders.verify-sms');
Route::post('/orders/{order}/resend/{type}', [OrderController::class, 'resendVerification'])->name('orders.resend')->middleware('throttle:verification');;
Route::post('/send-verification-code', [OrderController::class, 'sendVerificationCode'])->name('send-verification-code');
