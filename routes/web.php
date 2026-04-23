<?php

use App\Actions\Cart\AddToCart;
use App\Actions\Cart\RemoveFromCart;
use App\Actions\Cart\UpdateCartItem;
use App\Actions\Coupons\ApplyCoupon;
use App\Actions\Orders\CreateOrder;
use App\Actions\Payments\VerifyPayment;
use App\Actions\Reviews\SubmitReview;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\StockAlertController;
use App\Http\Controllers\WishlistController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Sitemap & robots
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

// Public routes
Route::get('/', [ProductController::class, 'home'])->name('home');
Route::get('/shop', [ProductController::class, 'index'])->name('shop');
Route::get('/shop/{product:slug}', [ProductController::class, 'show'])->name('product.show');
Route::get('/search', SearchController::class)->name('search');

Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{post:slug}', [BlogController::class, 'show'])->name('blog.show');

// Cart (session-based, no auth required)
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/add', AddToCart::class)->name('add');
    Route::patch('/update/{item}', UpdateCartItem::class)->name('update');
    Route::delete('/remove/{item}', RemoveFromCart::class)->name('remove');
    Route::post('/coupon', ApplyCoupon::class)->name('coupon');
});

// Checkout (guest-friendly)
Route::prefix('checkout')->name('checkout.')->group(function () {
    Route::get('/', [CheckoutController::class, 'index'])->name('index');
    Route::post('/', CreateOrder::class)->name('process');
    Route::get('/success/{order}', [CheckoutController::class, 'success'])->name('success');
    Route::get('/callback/{gateway}', VerifyPayment::class)->name('callback');
});

// Account
Route::middleware('auth')->prefix('account')->name('account.')->group(function () {
    Route::get('/', [OrderController::class, 'index'])->name('index');
    Route::get('/orders', [OrderController::class, 'index'])->name('orders');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
});

// Reviews (requires auth)
Route::middleware('auth')->post('/reviews', SubmitReview::class)->name('reviews.store');

// Wishlist (requires auth)
Route::middleware('auth')->post('/wishlist/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');

// Back-in-stock alerts (no auth required)
Route::post('/stock-alerts', [StockAlertController::class, 'store'])->name('stock-alerts.store');

// Locale switcher
Route::post('/locale', function (Request $request) {
    if (in_array($request->locale, ['en', 'el'])) {
        session(['locale' => $request->locale]);
    }

    return back();
})->name('locale.set');

// Impersonation routes
Route::impersonate();

// Auth routes
require __DIR__.'/auth.php';
