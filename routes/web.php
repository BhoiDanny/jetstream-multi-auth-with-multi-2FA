<?php

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\Auth\AdminAuthenticationSessionController;
use App\Http\Controllers\Admin\TwoFactorAuthenticatedSessionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::middleware('admin:admin')->group(function(){
   Route::get('admin/login', [AdminAuthenticationSessionController::class, 'loginForm']);
    Route::post('admin/login', [AdminAuthenticationSessionController::class, 'store'])->name('admin.login');
});

Route::middleware([
    'auth:sanctum,admin',
    'auth:admin',
    config('jetstream.auth_session'),
    'verified'
])->group(function () {
    Route::get('/admin/dashboard', function () {
        return view('dashboard');
    })->name('admin.dashboard');

    Route::get('/admin/user/profile', [AdminUserController::class, 'profile'])->name('admin.profile.show');


});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});



Route::get('/admin/two-factor-challenge', [TwoFactorAuthenticatedSessionController::class, 'create'])
    ->middleware(['guest:'.config('fortify.guard')])
    ->name('admin.two-factor.login');

Route::post('/admin/two-factor-challenge', [TwoFactorAuthenticatedSessionController::class, 'store'])
    ->middleware(array_filter([
        'guest:'.config('fortify.guard'),
        config('fortify.limiters.two-factor') ? 'throttle:'.config('fortify.limiters.two-factor') : null,
    ]));
