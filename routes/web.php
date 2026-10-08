<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MovementController;
use App\Http\Controllers\PartieController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PricingSettingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return 'hallll';
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');

    Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');

    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::view('/subscription/suspended', 'subscription.suspended')
    ->middleware('auth')
    ->name('subscription.suspended');

Route::get('/dashboard', function (Request $request) {
    abort_unless($request->user()->role === 'company_admin', 403);

    return view('dashboard', ['isSuperAdmin' => false]);
})->middleware('auth')->name('dashboard');

Route::get('/admin/dashboard', function (Request $request) {
    abort_unless($request->user()->role === 'super_admin', 403);

    return view('dashboard', ['isSuperAdmin' => true]);
})->middleware('auth')->name('admin.dashboard');

Route::middleware(['auth', 'subscription', 'company.admin'])->group(function () {
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::patch('/categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('categories.toggle-status');

    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::patch('/products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('products.toggle-status');

    Route::get('/parties', [PartieController::class, 'index'])->name('parties.index');
    Route::post('/parties', [PartieController::class, 'store'])->name('parties.store');
    Route::put('/parties/{party}', [PartieController::class, 'update'])->name('parties.update');
    Route::patch('/parties/{party}/toggle-status', [PartieController::class, 'toggleStatus'])->name('parties.toggle-status');

    Route::get('/settings/pricing', [PricingSettingController::class, 'edit'])->name('settings.pricing.edit');
    Route::put('/settings/pricing', [PricingSettingController::class, 'update'])->name('settings.pricing.update');

    Route::get('/movements', [MovementController::class, 'index'])->name('movements.index');
    Route::get('/movements/create', [MovementController::class, 'create'])->name('movements.create');
    Route::post('/movements', [MovementController::class, 'store'])->name('movements.store');
    Route::get('/movements/{movement}', [MovementController::class, 'show'])->name('movements.show');
});
