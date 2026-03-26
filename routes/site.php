<?php

use App\Http\Controllers\Site\SiteAuthController;
use App\Http\Controllers\Site\SiteCartController;
use App\Http\Controllers\Site\SiteOrderController;
use App\Http\Controllers\Site\SiteController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SiteController::class, 'home'])->name('site.home');
Route::get('products/{id}', [SiteController::class, 'showProduct'])->name('site.products.show');
Route::middleware('guest:customer')->group(function () {
    Route::get('login', [SiteAuthController::class, 'showLogin'])->name('site.login');
    Route::post('login', [SiteAuthController::class, 'login'])->name('site.login.submit');
    Route::get('register', [SiteAuthController::class, 'showRegister'])->name('site.register');
    Route::post('register', [SiteAuthController::class, 'register'])->name('site.register.submit');
});
Route::middleware('customer.auth')->group(function () {
    Route::post('logout', [SiteAuthController::class, 'logout'])->name('site.logout');
    Route::get('cart', [SiteCartController::class, 'index'])->name('site.cart.index');
    Route::post('cart/items', [SiteCartController::class, 'store'])->name('site.cart.items.store');
    Route::patch('cart/items/{id}', [SiteCartController::class, 'update'])->name('site.cart.items.update');
    Route::delete('cart/items/{id}', [SiteCartController::class, 'destroy'])->name('site.cart.items.destroy');
    Route::get('checkout', [SiteOrderController::class, 'checkout'])->name('site.checkout');
    Route::post('checkout', [SiteOrderController::class, 'placeOrder'])->name('site.checkout.submit');
    Route::get('orders', [SiteOrderController::class, 'index'])->name('site.orders.index');
    Route::get('orders/{id}', [SiteOrderController::class, 'show'])->name('site.orders.show');
});
