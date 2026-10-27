<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CompanySettingsController;
use App\Http\Controllers\MovementController;
use App\Http\Controllers\PartieController;
use App\Http\Controllers\PricingSettingController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportExportController;
use App\Http\Controllers\SuperAdmin\CompanyController as SuperAdminCompanyController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboardController;
use App\Http\Controllers\SuperAdmin\UserController as SuperAdminUserController;
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

    $company = $request->user()->company;
    $trialDaysRemaining = $company?->status === 'trial' && $company->trial_ends_at
        ? today()->diffInDays($company->trial_ends_at, false)
        : null;

    return view('dashboard', [
        'isSuperAdmin' => false,
        'company' => $company,
        'trialDurationDays' => config('subscription.trial_days', 14),
        'trialDaysRemaining' => $trialDaysRemaining,
    ]);
})->middleware('auth')->name('dashboard');

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'super.admin'])
    ->group(function () {
        Route::get('/dashboard', [SuperAdminDashboardController::class, 'index'])->name('dashboard');

        Route::get('/companies', [SuperAdminCompanyController::class, 'index'])->name('companies.index');
        Route::get('/companies/{company}/edit', [SuperAdminCompanyController::class, 'edit'])->name('companies.edit');
        Route::put('/companies/{company}', [SuperAdminCompanyController::class, 'update'])->name('companies.update');
        Route::post('/companies/{company}/extend-trial', [SuperAdminCompanyController::class, 'extendTrial'])->name('companies.extend-trial');
        Route::post('/companies/{company}/terminate-subscription', [SuperAdminCompanyController::class, 'terminateSubscription'])->name('companies.terminate-subscription');

        Route::get('/users', [SuperAdminUserController::class, 'index'])->name('users.index');
    });

Route::middleware(['auth', 'subscription', 'company.admin'])->group(function () {
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::patch('/categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('categories.toggle-status');

    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/export/excel', [ProductController::class, 'exportExcel'])->name('products.export.excel');
    Route::post('/products/import', [ProductController::class, 'import'])->name('products.import');
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::patch('/products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('products.toggle-status');

    Route::get('/parties', [PartieController::class, 'index'])->name('parties.index');
    Route::post('/parties', [PartieController::class, 'store'])->name('parties.store');
    Route::put('/parties/{party}', [PartieController::class, 'update'])->name('parties.update');
    Route::patch('/parties/{party}/toggle-status', [PartieController::class, 'toggleStatus'])->name('parties.toggle-status');

    Route::get('/settings', [CompanySettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings/company', [CompanySettingsController::class, 'update'])->name('settings.company.update');
    Route::get('/settings/pricing', [PricingSettingController::class, 'edit'])->name('settings.pricing.edit');
    Route::put('/settings/pricing', [PricingSettingController::class, 'update'])->name('settings.pricing.update');

    Route::get('/movements', [MovementController::class, 'index'])->name('movements.index');
    Route::get('/movements/create', [MovementController::class, 'create'])->name('movements.create');
    Route::post('/movements', [MovementController::class, 'store'])->name('movements.store');
    Route::post('/movements/export/pdf', [MovementController::class, 'exportPdf'])->name('movements.export.pdf');
    Route::get('/movements/export/excel', [MovementController::class, 'exportExcel'])->name('movements.export.excel');
    Route::get('/movements/{movement}/pdf', [MovementController::class, 'exportInvoicePdf'])->whereNumber('movement')->name('movements.pdf');
    Route::post('/movements/{movement}/confirm', [MovementController::class, 'confirmDraft'])->whereNumber('movement')->name('movements.confirm');
    Route::get('/movements/{movement}', [MovementController::class, 'show'])->whereNumber('movement')->name('movements.show');
    Route::get('/reports', [ReportExportController::class, 'index'])->name('reports.index');
    Route::get('/reports/{report}/download', [ReportExportController::class, 'download'])->name('reports.download');
});
