<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ServiceCategoryController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Provider\DashboardController as ProviderDashboardController;
use App\Http\Controllers\ServiceRequestController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public pages: F1 catalog
|--------------------------------------------------------------------------
*/

Route::get('/', [CatalogController::class, 'index'])->name('home');
Route::get('/services', [CatalogController::class, 'search'])->name('catalog.search');
Route::get('/categories/{category:slug}', [CatalogController::class, 'category'])->name('catalog.category');

/*
|--------------------------------------------------------------------------
| Guests: register, log in, reset password (supporting work)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);

    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:login');

    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->middleware('throttle:6,1')->name('password.email');
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

/*
|--------------------------------------------------------------------------
| Logged in, before email verification
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'send'])
        ->middleware('throttle:6,1')->name('verification.send');
});

/*
|--------------------------------------------------------------------------
| Logged in, active and verified
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'active', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/account', [AccountController::class, 'edit'])->name('account.edit');
    Route::put('/account', [AccountController::class, 'update'])->name('account.update');
    Route::put('/account/password', [AccountController::class, 'updatePassword'])->name('account.password');
    Route::post('/account/deactivate', [AccountController::class, 'deactivate'])->name('account.deactivate');

    // Customers: dashboard and F4 service requests
    Route::middleware('role:customer')->group(function () {
        Route::get('/customer/dashboard', CustomerDashboardController::class)->name('customer.dashboard');

        Route::resource('requests', ServiceRequestController::class)
            ->except('destroy')
            ->parameters(['requests' => 'serviceRequest']);
        Route::post('/requests/{serviceRequest}/cancel', [ServiceRequestController::class, 'cancel'])->name('requests.cancel');
    });

    // Providers: F13, F14 and F17 add their pages here
    Route::middleware('role:provider')->prefix('provider')->name('provider.')->group(function () {
        Route::get('/dashboard', ProviderDashboardController::class)->name('dashboard');
    });

    // Admins: dashboard and F1 catalog management
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');
        Route::resource('categories', ServiceCategoryController::class)->except('show');
        Route::resource('services', ServiceController::class)->except('show');
    });
});
