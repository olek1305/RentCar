<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\CarAdminController;
use App\Http\Controllers\Admin\CurrencyController;
use App\Http\Controllers\Admin\OrderAdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/cars', [CarController::class, 'index'])->name('cars.index');
Route::get('/condition', [HomeController::class, 'condition'])->name('condition');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
Route::post('/contact', [ContactController::class, 'send'])->middleware('throttle:5,1')->name('contact.send');

Route::get('/admin/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/admin/login', [AuthController::class, 'login'])->middleware('throttle:login')->name('login.store');

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/cars/create', [CarController::class, 'create'])->name('cars.create');
    Route::get('/cars/{car}/edit', [CarController::class, 'edit'])->name('cars.edit');
    Route::post('/cars', [CarController::class, 'store'])->name('cars.store');
    Route::put('/cars/{car}', [CarController::class, 'update'])->name('cars.update');
    Route::delete('/cars/{car}', [CarController::class, 'destroy'])->name('cars.destroy');
    Route::patch('/cars/{car}/toggle-visibility', [CarController::class, 'toggleVisibility'])
        ->name('cars.toggle-visibility');
});

Route::get('/cars/{car}', [CarController::class, 'show'])->name('cars.show');

Route::post('/orders', [OrderController::class, 'store'])->middleware('throttle:10,1')->name('orders.store');

Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('admin.index');

    // Car management
    Route::get('/cars', [CarAdminController::class, 'index'])->name('admin.cars.index');

    // Currency management
    Route::get('/currencies', [CurrencyController::class, 'index'])->name('admin.currencies.index');
    Route::post('/currencies', [CurrencyController::class, 'store'])->name('admin.currencies.store');
    Route::put('/currencies/{currency}', [CurrencyController::class, 'update'])->name('admin.currencies.update');
    Route::delete('/currencies/{currency}', [CurrencyController::class, 'destroy'])->name('admin.currencies.destroy');
    Route::patch('/currencies/{currency}/set-default', [CurrencyController::class, 'setDefault'])->name('admin.currencies.set-default');

    Route::get('/orders', [OrderAdminController::class, 'index'])->name('admin.orders.index');
    Route::get('/orders/{order}', [OrderAdminController::class, 'show'])->name('admin.orders.show');
    Route::patch('/orders/{order}/status', [OrderAdminController::class, 'updateStatus'])
        ->name('admin.orders.update-status');
    Route::post('/orders/{order}/send-payment-link', [OrderAdminController::class, 'sendPaymentLink'])
        ->name('admin.orders.send-payment-link');
    Route::post('/orders/{order}/renew-email-token', [OrderAdminController::class, 'renewEmailToken'])
        ->name('admin.orders.renew-email-token');
    Route::post('/orders/{order}/renew-sms-token', [OrderAdminController::class, 'renewSmsToken'])
        ->name('admin.orders.renew-sms-token');
    Route::patch('/orders/{order}/mark-finished', [OrderAdminController::class, 'markAsFinished'])
        ->name('admin.orders.mark-finished');
    Route::patch('/orders/{order}/cancel', [OrderAdminController::class, 'cancelOrder'])
        ->name('admin.orders.cancel');
    Route::post('/orders/{order}/send-final-payment-link', [OrderAdminController::class, 'sendFinalPaymentLink'])
        ->name('admin.orders.send-final-payment-link');
    Route::post('/orders/{order}/resend-confirmation-email', [OrderAdminController::class, 'resendConfirmationEmail'])
        ->name('admin.orders.resend-confirmation-email');
    Route::post('/logout', [AuthController::class, 'logout'])->name('admin.logout');
});

Route::get('/orders/{order}/verify-email-payment/{token}', [OrderController::class, 'verifyEmailForPayment'])
    ->middleware('throttle:verification')
    ->name('orders.verify-email-payment');

Route::get('/payment/success/{order}', [PaymentController::class, 'success'])->name('payment.success');
Route::get('/payment/cancel/{order}', [PaymentController::class, 'cancel'])->name('payment.cancel')->middleware('signed');

// Stripe Webhooks (excluded from CSRF in bootstrap/app.php)
Route::post('/webhooks/stripe', [WebhookController::class, 'handleStripe'])->name('webhooks.stripe');
