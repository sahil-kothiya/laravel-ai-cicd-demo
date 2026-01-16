<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::get('/', function () {
    return view('welcome');
});

// User routes for testing
Route::get('/users', [UserController::class, 'index']);
Route::post('/users', [UserController::class, 'store']);
Route::get('/users/{id}', [UserController::class, 'show']);
Route::put('/users/{id}', [UserController::class, 'update']);
Route::delete('/users/{id}', [UserController::class, 'destroy']);
Route::post('/users/bulk', [UserController::class, 'bulkStore']);

// Product routes
Route::get('/products', [\App\Http\Controllers\ProductController::class, 'index']);
Route::post('/products', [\App\Http\Controllers\ProductController::class, 'store']);
Route::get('/products/{id}', [\App\Http\Controllers\ProductController::class, 'show']);
Route::put('/products/{id}', [\App\Http\Controllers\ProductController::class, 'update']);
Route::delete('/products/{id}', [\App\Http\Controllers\ProductController::class, 'destroy']);

// Order routes
Route::get('/orders', [\App\Http\Controllers\OrderController::class, 'index']);
Route::post('/orders', [\App\Http\Controllers\OrderController::class, 'store']);
Route::get('/orders/{id}', [\App\Http\Controllers\OrderController::class, 'show']);
Route::put('/orders/{id}', [\App\Http\Controllers\OrderController::class, 'update']);
Route::delete('/orders/{id}', [\App\Http\Controllers\OrderController::class, 'destroy']);
