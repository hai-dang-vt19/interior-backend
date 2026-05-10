<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductReviewController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\AuthAdminController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthAdminController::class, 'showLoginForm'])->name('admin.login');
        Route::post('login', [AuthAdminController::class, 'login'])->name('admin.login.submit');
        // Route::get('register', [AuthAdminController::class, 'showRegisterForm'])->name('admin.register');
        // Route::post('register', [AuthAdminController::class, 'register'])->name('admin.register.submit');
    });

    Route::middleware(['auth:web'])->group(function () {
        Route::middleware('role:ADMIN,STAFF')->group(function () {
            Route::get('', [DashboardController::class, 'index'])->name('admin.dashboard');
            Route::get('dashboard/export-revenue', [DashboardController::class, 'exportRevenue'])->name('admin.dashboard.export-revenue');
            Route::get('change-password', [AuthAdminController::class, 'showChangePasswordForm'])->name('admin.change-password');
            Route::post('change-password', [AuthAdminController::class, 'changePassword'])->name('admin.change-password.submit');
            Route::get('auth-activity-logs', [AuthAdminController::class, 'activityLogs'])->name('admin.auth-activity-logs');
        });

        Route::middleware('role:ADMIN,STAFF')->prefix('customer')->name('admin.customer.')->group(function () {
            Route::post('', [CustomerController::class, 'store'])->name('store');
            Route::get('{id}/profile', [CustomerController::class, 'profile'])->name('profile');
            Route::post('{id}/address', [CustomerController::class, 'addAddress'])->name('address.store');
            Route::delete('{id}/address/{addressId}', [CustomerController::class, 'deleteAddress'])->name('address.destroy');
            Route::post('{id}/contact', [CustomerController::class, 'addContact'])->name('contact.store');
            Route::patch('restore/{id}', [CustomerController::class, 'restore'])->name('restore');
            Route::delete('force-destroy/{id}', [CustomerController::class, 'forceDelete'])->name('force-destroy');
            Route::get('{id}', [CustomerController::class, 'edit'])->name('edit');
            Route::post('{id}', [CustomerController::class, 'update'])->name('update');
            Route::delete('destroy/{id}', [CustomerController::class, 'destroy'])->name('destroy');
            Route::get('', [CustomerController::class, 'index'])->name('index');
        });

        Route::middleware('role:ADMIN')->prefix('product')->name('admin.product.')->group(function () {
            Route::post('', [ProductController::class, 'store'])->name('store');
            Route::post('banner-products', [ProductController::class, 'updateBannerProducts'])->name('banner-products.update');
            Route::get('{id}/images', [ProductController::class, 'images'])->name('images');
            Route::post('{id}/images', [ProductController::class, 'storeImage'])->name('images.store');
            Route::patch('{id}/images/{imageId}/primary', [ProductController::class, 'setPrimaryImage'])->name('images.primary');
            Route::delete('{id}/images/{imageId}', [ProductController::class, 'destroyImage'])->name('images.destroy');
            Route::get('{id}/inventory', [ProductController::class, 'inventory'])->name('inventory');
            Route::post('{id}/inventory', [ProductController::class, 'adjustInventory'])->name('inventory.adjust');
            Route::patch('restore/{id}', [ProductController::class, 'restore'])->name('restore');
            Route::delete('force-destroy/{id}', [ProductController::class, 'forceDelete'])->name('force-destroy');
            Route::get('{id}', [ProductController::class, 'edit'])->name('edit');
            Route::post('{id}', [ProductController::class, 'update'])->name('update');
            Route::delete('destroy/{id}', [ProductController::class, 'destroy'])->name('destroy');
            Route::get('', [ProductController::class, 'index'])->name('index');
        });

        Route::middleware('role:ADMIN')->prefix('product-review')->name('admin.product-review.')->group(function () {
            Route::get('{id}/edit', [ProductReviewController::class, 'edit'])->name('edit');
            Route::patch('{id}', [ProductReviewController::class, 'update'])->name('update');
            Route::delete('{id}', [ProductReviewController::class, 'destroy'])->name('destroy');
            Route::get('', [ProductReviewController::class, 'index'])->name('index');
        });

        Route::middleware('role:ADMIN')->prefix('category')->name('admin.category.')->group(function () {
            Route::post('', [CategoryController::class, 'store'])->name('store');
            Route::patch('restore/{id}', [CategoryController::class, 'restore'])->name('restore');
            Route::delete('force-destroy/{id}', [CategoryController::class, 'forceDelete'])->name('force-destroy');
            Route::get('{id}', [CategoryController::class, 'edit'])->name('edit');
            Route::post('{id}', [CategoryController::class, 'update'])->name('update');
            Route::delete('destroy/{id}', [CategoryController::class, 'destroy'])->name('destroy');
            Route::get('', [CategoryController::class, 'index'])->name('index');
        });

        Route::middleware('role:ADMIN,STAFF')->prefix('order')->name('admin.order.')->group(function () {
            Route::get('pending-notifications', [OrderController::class, 'pendingNotifications'])->name('pending-notifications');
            Route::post('', [OrderController::class, 'store'])->name('store');
            Route::get('{id}/show', [OrderController::class, 'show'])->name('show');
            Route::get('{id}/invoice', [OrderController::class, 'invoice'])->name('invoice');
            Route::patch('{id}/shipping', [OrderController::class, 'updateShipping'])->name('shipping.update');
            Route::post('{id}/return-request', [OrderController::class, 'storeReturnRequest'])->name('return.store');
            Route::patch('{id}/return-request/{returnId}', [OrderController::class, 'updateReturnRequestStatus'])->name('return.update');
            Route::patch('restore/{id}', [OrderController::class, 'restore'])->name('restore');
            Route::delete('force-destroy/{id}', [OrderController::class, 'forceDelete'])->name('force-destroy');
            Route::get('{id}', [OrderController::class, 'edit'])->name('edit');
            Route::post('{id}', [OrderController::class, 'update'])->name('update');
            Route::delete('destroy/{id}', [OrderController::class, 'destroy'])->name('destroy');
            Route::get('', [OrderController::class, 'index'])->name('index');
        });

        Route::middleware('role:ADMIN')->prefix('staff')->name('admin.staff.')->group(function () {
            Route::post('', [StaffController::class, 'store'])->name('store');
            Route::patch('restore/{id}', [StaffController::class, 'restore'])->name('restore');
            Route::delete('force-destroy/{id}', [StaffController::class, 'forceDelete'])->name('force-destroy');
            Route::get('{id}', [StaffController::class, 'edit'])->name('edit');
            Route::post('{id}', [StaffController::class, 'update'])->name('update');
            Route::delete('destroy/{id}', [StaffController::class, 'destroy'])->name('destroy');
            Route::get('', [StaffController::class, 'index'])->name('index');
        });

        Route::post('logout', [AuthAdminController::class, 'logout'])->name('admin.logout');
    });
});
