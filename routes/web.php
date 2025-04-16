<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthAdminController;

Route::prefix('admin')->group(function () {

    // Route guest
    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthAdminController::class, 'showLoginForm'])->name('admin.login');
        Route::post('login', [AuthAdminController::class, 'login'])->name('admin.login.submit');
    });

    
    // Protected routes
    Route::middleware(['auth:web', 'admin'])->group(function () {
        Route::get('', function () {
            return view('dashboard.index');
        })->name('admin.dashboard');
        
        Route::post('logout', [AuthAdminController::class, 'logout'])->name('admin.logout');
    });
});