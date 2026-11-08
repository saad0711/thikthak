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
use App\Http\Controllers\Provider\AvailabilityController;
use App\Http\Controllers\Provider\DashboardController as ProviderDashboardController;
use App\Http\Controllers\Provider\DayOffController;
use App\Http\Controllers\Provider\OnlineStatusController;
use App\Http\Controllers\Provider\ScheduleController;
use App\Http\Controllers\ServiceRequestController;
use App\Http\Controllers\CertificationController;
use App\Http\Controllers\PortfolioItemController;
use App\Http\Controllers\ProviderProfileController;
use App\Http\Controllers\ProviderServiceController;
use App\Http\Controllers\ServiceAreaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public pages: F1 catalog
|--------------------------------------------------------------------------
*/

Route::get('/', [CatalogController::class, 'index'])->name('home');
Route::get('/services', [CatalogController::class, 'search'])->name('catalog.search');
Route::get('/categories/{category:slug}', [CatalogController::class, 'category'])->name('catalog.category');
// F13: Public provider profile
Route::get('/providers/{providerProfile}', [ProviderProfileController::class, 'show'])
    ->name('providers.show');

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

        // F14: Availability & Schedule Management
        Route::get('/schedule', [ScheduleController::class, 'week'])->name('schedule.week');
        Route::get('/availability', [AvailabilityController::class, 'edit'])->name('availability.edit');
        Route::put('/availability', [AvailabilityController::class, 'update'])->name('availability.update');
        Route::patch('/online-status', [OnlineStatusController::class, 'toggle'])->name('online-status.toggle');
        Route::get('/days-off', [DayOffController::class, 'index'])->name('days-off.index');
        Route::post('/days-off', [DayOffController::class, 'store'])->name('days-off.store');
        Route::delete('/days-off/{dayOff}', [DayOffController::class, 'destroy'])->name('days-off.destroy');

        // F13: Professional Profile & Portfolio
        Route::get('/profile', [ProviderProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [ProviderProfileController::class, 'update'])->name('profile.update');
        
        Route::put('/profile/services', [ProviderServiceController::class, 'update'])->name('services.update');
        
        Route::put('/profile/area', [ServiceAreaController::class, 'update'])->name('area.update');
        
        Route::post('/certifications', [CertificationController::class, 'store'])->name('certifications.store');
        Route::get('/certifications/{certification}/edit', [CertificationController::class, 'edit'])->name('certifications.edit');
        Route::get('/certifications/{certification}/file', [CertificationController::class, 'download'])->name('certifications.file');
        Route::put('/certifications/{certification}', [CertificationController::class, 'update'])->name('certifications.update');
        Route::delete('/certifications/{certification}', [CertificationController::class, 'destroy'])->name('certifications.destroy');
        
        Route::post('/portfolio', [PortfolioItemController::class, 'store'])->name('portfolio.store');
        Route::delete('/portfolio/{portfolioItem}', [PortfolioItemController::class, 'destroy'])->name('portfolio.destroy');
    });

    // Admins: dashboard and F1 catalog management
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');
        Route::resource('categories', ServiceCategoryController::class)->except('show');
        Route::resource('services', ServiceController::class)->except('show');
    });
});
