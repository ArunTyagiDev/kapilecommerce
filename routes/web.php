<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
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
Route::get('/products/load', [FrontendProductController::class, 'load'])->name('products.load');
Route::get('/products/filters', [FrontendProductController::class, 'filters'])->name('products.filters');
Route::get('/products/{slug}', [FrontendProductController::class, 'show'])->name('products.show');
Route::post('/products/variant', [FrontendProductController::class, 'getVariant'])->name('products.variant');

// Custom photo products (OMGS-style)
Route::get('/customise', [App\Http\Controllers\Frontend\CustomiseController::class, 'index'])->name('customise.index');
Route::get('/customise/{categorySlug}', [App\Http\Controllers\Frontend\CustomiseController::class, 'show'])->name('customise.hub');
Route::get('/custom/{slug}', [App\Http\Controllers\Frontend\CustomProductController::class, 'show'])->name('custom.product');

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

/*
| Shared hosting: create public/storage → storage/app/public without SSH.
| Set STORAGE_SETUP_TOKEN in .env, then visit once:
| https://your-domain.com/setup/storage-link?token=YOUR_SECRET
| Remove or change the token after success.
*/
Route::get('/setup/storage-link', function (Request $request) {
    $expected = env('STORAGE_SETUP_TOKEN');
    if (empty($expected)) {
        abort(503, 'Add STORAGE_SETUP_TOKEN=your-secret to .env on the server first.');
    }

    $token = (string) $request->query('token', '');
    if ($token === '' || ! hash_equals($expected, $token)) {
        abort(403, 'Invalid or missing token.');
    }

    $link = public_path('storage');
    $target = storage_path('app/public');

    if (! is_dir($target)) {
        mkdir($target, 0755, true);
    }

    if (is_link($link)) {
        return response()->json([
            'ok' => true,
            'message' => 'Storage symlink already exists.',
            'link' => $link,
            'target' => readlink($link),
        ]);
    }

    if (file_exists($link)) {
        return response()->json([
            'ok' => false,
            'message' => 'public/storage exists but is not a symlink. Remove it in File Manager, then run this URL again.',
            'path' => $link,
        ], 409);
    }

    try {
        Artisan::call('storage:link');
        $output = trim(Artisan::output());

        return response()->json([
            'ok' => true,
            'message' => 'Storage symlink created.',
            'output' => $output,
            'link' => $link,
            'target' => $target,
        ]);
    } catch (\Throwable $e) {
        $relativeTarget = '../storage/app/public';
        if (@symlink($relativeTarget, $link)) {
            return response()->json([
                'ok' => true,
                'message' => 'Storage symlink created (relative path).',
                'link' => $link,
                'target' => $relativeTarget,
            ]);
        }

        if (@symlink($target, $link)) {
            return response()->json([
                'ok' => true,
                'message' => 'Storage symlink created (absolute path).',
                'link' => $link,
                'target' => $target,
            ]);
        }

        return response()->json([
            'ok' => false,
            'message' => $e->getMessage(),
        ], 500);
    }
})->name('setup.storage-link');

require __DIR__.'/auth.php';
