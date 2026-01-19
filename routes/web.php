<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Users Resource Routes
Route::resource('users', UserController::class);
Route::post('users/{id}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggleStatus');

// Products Resource Routes
Route::resource('products', ProductController::class);
Route::post('products/{id}/update-stock', [ProductController::class, 'updateStock'])->name('products.update-stock');

// Orders Resource Routes
Route::resource('orders', OrderController::class);
Route::post('orders/{id}/update-status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
