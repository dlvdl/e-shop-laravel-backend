<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Shop\CustomerAuthController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Shop\CartController;
use App\Http\Controllers\Shop\CustomerProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum', 'admin', 'cors'])->prefix('')->group(function () {
    Route::get('/', [AuthController::class, 'getUser']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::apiResource('/products', ProductController::class);
});

// Route::post('shop/login', [CustomerAuthController::class, 'login']);

// Route::middleware(['auth:sanctum'])->prefix('shop')->group(function() {
//     Route::post('/logout', [CustomerAuthController::class, 'logout']);
// });

Route::middleware(['guest'])->prefix('/shop')->group(function () {
    Route::get('/products', [CustomerProductController::class, 'index']);
    Route::get('products/{product:slug}', [CustomerProductController::class, 'show']);

    Route::prefix('/cart')->group(function () {
        Route::get('', [CartController::class, 'index']);
        Route::post('add/{product:slug}', [CartController::class, 'addToCart']);
        Route::delete('remove/{product:slug}', [CartController::class, 'removeFromCart']);
        Route::post('update-quantity/{product:slug}', [CartController::class, 'updateQuantityInCart']);
    });
});




