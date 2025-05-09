<?php

use App\Http\Controllers\Admin\CustomerController;
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

        // Dashboard
        Route::get('', function () {
            return view('dashboard.index');
        })->name('admin.dashboard');

        // Customer
        Route::prefix('customer')->name('admin.customer.')->group(function () {
            Route::get('{id}', [CustomerController::class, 'edit'])->name('edit');
            Route::post('{id}', [CustomerController::class, 'update'])->name('update');
            Route::delete('destroy/{id}', [CustomerController::class, 'destroy'])->name('destroy');
            Route::get('', [CustomerController::class, 'index'])->name('index');
        });
        
        Route::post('logout', [AuthAdminController::class, 'logout'])->name('admin.logout');
    });
});