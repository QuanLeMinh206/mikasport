<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SizeController;
use App\Http\Controllers\SizeProductController;
use App\Models\Category;

// Định nghĩa API routes ở đây
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::apiResource('products', ProductController::class);
Route::get('products/search', [ProductController::class, 'search']);
Route::apiResource('categories', CategoryController::class);