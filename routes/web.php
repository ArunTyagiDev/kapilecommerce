<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Frontend\ProductController as FrontendProductController;
use App\Http\Controllers\Frontend\CartController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Frontend Routes
Route::get('/', [App\Http\Controllers\Frontend\HomeController::class, 'index'])->name('home');

Route::get('/products', [FrontendProductController::class, 'index'])->name('products.index');
Route::get('/products/{slug}', [FrontendProductController::class, 'show'])->name('products.show');
Route::post('/products/variant', [FrontendProductController::class, 'getVariant'])->name('products.variant');

// Cart Routes
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/add', [CartController::class, 'add'])->name('add');
    Route::put('/{cart}', [CartController::class, 'update'])->name('update');
    Route::delete('/{cart}', [CartController::class, 'destroy'])->name('destroy');
});

// Checkout Routes
Route::prefix('checkout')->name('checkout.')->group(function () {
    Route::get('/', [App\Http\Controllers\Frontend\CheckoutController::class, 'index'])->name('index');
    Route::post('/', [App\Http\Controllers\Frontend\CheckoutController::class, 'store'])->name('store');
});

// User Dashboard Routes (requires auth)
Route::prefix('user')->name('user.')->middleware('auth')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Frontend\UserDashboardController::class, 'index'])->name('dashboard');
    Route::get('/orders', [App\Http\Controllers\Frontend\UserDashboardController::class, 'orders'])->name('orders');
    Route::get('/orders/{id}', [App\Http\Controllers\Frontend\UserDashboardController::class, 'orderDetails'])->name('order-details');
    Route::get('/profile', [App\Http\Controllers\Frontend\UserDashboardController::class, 'profile'])->name('profile');
    Route::put('/profile', [App\Http\Controllers\Frontend\UserDashboardController::class, 'updateProfile'])->name('profile.update');
    Route::post('/address', [App\Http\Controllers\Frontend\UserDashboardController::class, 'saveAddress'])->name('address.save');
    Route::delete('/address/{id}', [App\Http\Controllers\Frontend\UserDashboardController::class, 'deleteAddress'])->name('address.delete');
});

// Vendor Routes
Route::prefix('vendor')->name('vendor.')->middleware(['auth', 'vendor'])->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Vendor\DashboardController::class, 'index'])->name('dashboard');
    Route::resource('products', App\Http\Controllers\Vendor\ProductController::class);
    Route::get('/products/category/{categoryId}/attributes', [App\Http\Controllers\Vendor\ProductController::class, 'getCategoryAttributes'])->name('products.category.attributes');
    Route::post('/products/{product}/generate-variants', [App\Http\Controllers\Vendor\ProductController::class, 'generateVariantsAction'])->name('products.generate-variants');
    Route::delete('/products/{product}/images/{image}', [App\Http\Controllers\Vendor\ProductController::class, 'destroyImage'])->name('products.images.destroy');
});

// Admin Routes
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    
    // Categories
    Route::resource('categories', App\Http\Controllers\Admin\CategoryController::class);
    Route::get('/categories/{category}/attributes', [App\Http\Controllers\Admin\CategoryController::class, 'attributes'])->name('categories.attributes');
    Route::post('/categories/{category}/attributes/attach', [App\Http\Controllers\Admin\CategoryController::class, 'attachAttribute'])->name('categories.attributes.attach');
    Route::delete('/categories/{category}/attributes/{attribute}', [App\Http\Controllers\Admin\CategoryController::class, 'detachAttribute'])->name('categories.attributes.detach');
    
    // Attributes
    Route::resource('attributes', App\Http\Controllers\Admin\AttributeController::class);
    Route::post('/attributes/{attribute}/values', [App\Http\Controllers\Admin\AttributeController::class, 'addValue'])->name('attributes.values.add');
    
    // Brands
    Route::get('/brands', [App\Http\Controllers\Admin\BrandController::class, 'index'])->name('brands.index');
    Route::post('/brands', [App\Http\Controllers\Admin\BrandController::class, 'store'])->name('brands.store');
    Route::put('/brands/{brand}', [App\Http\Controllers\Admin\BrandController::class, 'update'])->name('brands.update');
    Route::delete('/brands/{brand}', [App\Http\Controllers\Admin\BrandController::class, 'destroy'])->name('brands.destroy');
    
    // Shops (Vendors)
    Route::resource('shops', App\Http\Controllers\Admin\ShopController::class)->except(['show', 'destroy']);

    // Products
    Route::resource('products', App\Http\Controllers\Admin\ProductController::class);
    Route::get('/products/category/{categoryId}/attributes', [App\Http\Controllers\Admin\ProductController::class, 'getCategoryAttributes'])->name('products.category.attributes');
    Route::post('/products/{product}/generate-variants', [App\Http\Controllers\Admin\ProductController::class, 'generateVariantsAction'])->name('products.generate-variants');
    Route::delete('/products/{product}/images/{image}', [App\Http\Controllers\Admin\ProductController::class, 'destroyImage'])->name('products.images.destroy');
    
    // Orders
    Route::resource('orders', App\Http\Controllers\Admin\OrderController::class)->only(['index', 'show']);
    Route::put('/orders/{order}/status', [App\Http\Controllers\Admin\OrderController::class, 'updateStatus'])->name('orders.update-status');
    
    // Payments
    Route::get('/payments', [App\Http\Controllers\Admin\PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/export', [App\Http\Controllers\Admin\PaymentController::class, 'export'])->name('payments.export');
});

require __DIR__.'/auth.php';

