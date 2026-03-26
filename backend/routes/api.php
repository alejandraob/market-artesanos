<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ArtisanController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\WishlistController;

// Public Endpoints
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/contact', [ContactController::class, 'send']);
Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
Route::get('/products/{productId}/reviews', [ReviewController::class, 'index']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);

Route::get('/artisans', [ArtisanController::class, 'index']);
Route::get('/artisans/{id}', [ArtisanController::class, 'show']);

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{identifier}', [ProductController::class, 'show']);
Route::post('/shipping-rates', [ProductController::class, 'shippingRates']);

// Cart endpoints (auth optional)
Route::get('/cart', [CartController::class, 'index']);
Route::post('/cart', [CartController::class, 'store']);
Route::put('/cart/{itemId}', [CartController::class, 'update']);
Route::delete('/cart/{itemId}', [CartController::class, 'destroy']);

// Authenticated Endpoints
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/resend-verification', [AuthController::class, 'resendVerification']);
    Route::post('/products/{productId}/reviews', [ReviewController::class, 'store']);
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);

    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::post('/wishlist/toggle', [WishlistController::class, 'toggle']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/user', [AuthController::class, 'updateProfile']);

    // Admin & Presidente routes
    Route::middleware('role:admin,presidente')->group(function () {
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{category}', [CategoryController::class, 'update']);
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

        Route::get('/admin/artisans', [ArtisanController::class, 'all']);
        Route::post('/artisans', [ArtisanController::class, 'store']);
        Route::put('/artisans/{artisan}', [ArtisanController::class, 'update']);
        Route::delete('/artisans/{artisan}', [ArtisanController::class, 'destroy']);

        Route::get('/admin/products', [ProductController::class, 'allForAdmin']);
        Route::post('/products', [ProductController::class, 'store']);
        Route::put('/products/{product}', [ProductController::class, 'update']);
        Route::patch('/products/{product}/toggle-active', [ProductController::class, 'toggleActive']);
        Route::delete('/products/{product}', [ProductController::class, 'destroy']);

        Route::get('/admin/clients', [ClientController::class, 'index']);
        Route::get('/admin/clients/{id}', [ClientController::class, 'show']);

        Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus']);
    });

    // Client/Order routes
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::post('/orders/checkout', [OrderController::class, 'checkout']);
});
