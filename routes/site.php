<?php

use App\Http\Controllers\Site\SiteAuthController;
use App\Http\Controllers\Site\SiteCartController;
use App\Http\Controllers\Site\SiteOrderController;
use App\Http\Controllers\Site\SiteController;
use App\Http\Controllers\Site\SiteProductReviewController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SiteController::class, 'home'])->name('site.home');
Route::get('products', [SiteController::class, 'products'])->name('site.products.index');
Route::get('products/{id}', [SiteController::class, 'showProduct'])->name('site.products.show');
Route::get('register/verify', [SiteAuthController::class, 'verifyRegistrationEmail'])->name('site.register.verify');

Route::middleware('guest:customer')->group(function () {
    Route::get('login', [SiteAuthController::class, 'showLogin'])->name('site.login');
    Route::post('login', [SiteAuthController::class, 'login'])->name('site.login.submit');
    Route::get('register', [SiteAuthController::class, 'showRegister'])->name('site.register');
    Route::post('register', [SiteAuthController::class, 'register'])->name('site.register.submit');
});
Route::middleware('customer.auth')->group(function () {
    Route::post('logout', [SiteAuthController::class, 'logout'])->name('site.logout');
    Route::get('account', [SiteController::class, 'account'])->name('site.account');
    Route::patch('account', [SiteController::class, 'updateAccount'])->name('site.account.update');
    Route::patch('account/password', [SiteController::class, 'updateAccountPassword'])->name('site.account.password.update');
    Route::post('account/addresses', [SiteController::class, 'storeAccountAddress'])->name('site.account.addresses.store');
    Route::patch('account/addresses/{id}', [SiteController::class, 'updateAccountAddress'])->name('site.account.addresses.update');
    Route::delete('account/addresses/{id}', [SiteController::class, 'destroyAccountAddress'])->name('site.account.addresses.destroy');
    Route::patch('account/addresses/{id}/default', [SiteController::class, 'setDefaultAccountAddress'])->name('site.account.addresses.default');
    Route::get('cart', [SiteCartController::class, 'index'])->name('site.cart.index');
    Route::post('cart/items', [SiteCartController::class, 'store'])->name('site.cart.items.store');
    Route::patch('cart/items/{id}', [SiteCartController::class, 'update'])->name('site.cart.items.update');
    Route::delete('cart/items/{id}', [SiteCartController::class, 'destroy'])->name('site.cart.items.destroy');
    Route::get('checkout', [SiteOrderController::class, 'checkout'])->name('site.checkout');
    Route::post('checkout', [SiteOrderController::class, 'placeOrder'])->name('site.checkout.submit');
    Route::get('orders', [SiteOrderController::class, 'index'])->name('site.orders.index');
    Route::get('orders/{id}', [SiteOrderController::class, 'show'])->name('site.orders.show');
    Route::post('orders/{id}/cancel', [SiteOrderController::class, 'cancel'])->name('site.orders.cancel');
    Route::post('orders/{id}/reorder', [SiteOrderController::class, 'reorder'])->name('site.orders.reorder');
    Route::post('products/{productId}/reviews', [SiteProductReviewController::class, 'store'])->name('site.products.reviews.store');
    Route::patch('products/{productId}/reviews/{reviewId}', [SiteProductReviewController::class, 'update'])->name('site.products.reviews.update');
});
