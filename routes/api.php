<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SizeController;
use App\Http\Controllers\SizeProductController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CartsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\VoucherController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Controllers\DasboardController;


// ========== Public Routes ==========
Route::get('/products/filter-by-size', [CategoryController::class, 'filterBySize']);
Route::get('/sizes', [SizeController::class, 'index']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('/verify/{email}', [AuthController::class, 'verify']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password/{token}', [AuthController::class, 'resetPassword']);
Route::get('/price', [ProductController::class, 'filterByPrice']);

Route::apiResource('products', ProductController::class);
Route::apiResource('categories', CategoryController::class);
Route::get('search', [ProductController::class, 'search'])->name('search');
Route::get('/users/search', [AuthController::class, 'search']);
Route::get('/product/{id}', [ProductController::class, 'showpro']);
Route::get('/products/{id}/related', [ProductController::class, 'relatedProducts']);
Route::get('/products/{proId}/comments', [CommentController::class, 'index']);
Route::get('/categories/{id}/products', [CategoryController::class, 'getProducts']);
Route::get('/top-products', [ProductController::class, 'topProducts']);
Route::get('/sale-product', [ProductController::class, 'saleproduct']);
Route::get('/male-product', [ProductController::class, 'maleProducts']);
Route::get('/fel-product', [ProductController::class, 'maleProducts']);
Route::get('/topmanproduct',[ProductController::class,'topsellman']);
Route::get('/topwonmenproduct',[ProductController::class,'topsellwomen']);

Route::get('/cate-color', [CategoryController::class, 'catecolor']);
Route::get('/products/filter/color', [CategoryController::class, 'filterByColor']);
Route::get('/products/sort/price-asc', [ProductController::class, 'sortByPriceAsc']);
Route::get('/products/gender/{gender}', [ProductController::class, 'getByGender']);




// ========== Protected Routes ==========
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::get('/user', fn(Request $request) => $request->user());
    Route::post('/logout', [AuthController::class, 'destroy']);
    Route::get('/profile', [AuthController::class, 'getUser']);
    Route::put('/profile', [AuthController::class, 'updateProfileApi']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);

    // Cart (for authenticated users)
    Route::get('/user/cart', [CartsController::class, 'index']);
    Route::post('/user/cart/items', [CartsController::class, 'store']);
    Route::post('/cart/add', [CartController::class, 'addToCarts'])->name('cart.add');
    Route::delete('/cart/{size_product_id}', [CartController::class, 'destroy']);
    Route::put('/cart/{cart_id}', [CartController::class, 'updateCart']);

    // Checkout / Payment
    Route::prefix('order')->group(function () {
        Route::get('/checkout', [CheckoutController::class, 'index']);
        Route::post('/checkout', [CheckoutController::class, 'post_checkout']);
        Route::post('/payment', [CheckoutController::class, 'vnpay_payment']);
        Route::get('/payment', [CheckoutController::class, 'vnpay_payment'])->name('api.payment.index');
        Route::get('/payment/callback', [CheckoutController::class, 'vnpay_callback']);
    });



    Route::prefix('comments')->middleware('auth:sanctum')->group(function () {
        Route::post('/products/{proId}/comments', [CommentController::class, 'store']);
        Route::put('/{comment}', [CommentController::class, 'update']);
        Route::delete('/{id}', [CommentController::class, 'destroy']);
    });

});

// ========== Admin Routes ==========