<?php

use App\Actions\Cart\AddToCart;
use App\Actions\Cart\RemoveFromCart;
use App\Actions\Cart\UpdateCartItem;
use App\Actions\Coupons\ApplyCoupon;
use App\Actions\GiftCards\RedeemGiftCard;
use App\Actions\Orders\CreateOrder;
use App\Actions\Orders\RequestReturn;
use App\Actions\Payments\VerifyPayment;
use App\Actions\Reviews\SubmitReview;
use App\Actions\Waitlist\JoinWaitlist;
use App\Http\Controllers\AccommodationController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SavedItemController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\StockAlertController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\WishlistController;
use App\Models\Accommodation;
use App\Models\Activity;
use App\Models\Product;
use App\Models\Vehicle;
use App\Settings\GeneralSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Sitemap & robots
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

$theme = fn () => app(GeneralSettings::class)->active_theme;

// Public routes — theme-aware dispatch
Route::get('/', function () use ($theme) {
    return match ($theme()) {
        'Activities' => app(ActivityController::class)->home(),
        'Bookings' => app(AccommodationController::class)->home(),
        'Cars' => app(VehicleController::class)->home(),
        default => app(ProductController::class)->home(),
    };
})->name('home');

Route::get('/shop', function (Request $request) use ($theme) {
    return match ($theme()) {
        'Activities' => app(ActivityController::class)->index($request),
        'Bookings' => app(AccommodationController::class)->index($request),
        'Cars' => app(VehicleController::class)->index($request),
        default => app(ProductController::class)->index($request),
    };
})->name('shop');

Route::get('/shop/{slug}', function (string $slug) use ($theme) {
    return match ($theme()) {
        'Activities' => app(ActivityController::class)->show(
            Activity::where('slug', $slug)->firstOrFail()
        ),
        'Bookings' => app(AccommodationController::class)->show(
            Accommodation::where('slug', $slug)->firstOrFail()
        ),
        'Cars' => app(VehicleController::class)->show(
            Vehicle::where('slug', $slug)->firstOrFail()
        ),
        default => app(ProductController::class)->show(
            Product::where('slug', $slug)->firstOrFail()
        ),
    };
})->name('product.show');

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
    Route::post('/gift-card', RedeemGiftCard::class)->name('gift-card');
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
    Route::get('/orders', [OrderController::class, 'orders'])->name('orders');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/{order}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');
    Route::get('/orders/{order}/tickets/{ticket}', [OrderController::class, 'ticket'])->name('orders.ticket');
    Route::post('/orders/{order}/returns', RequestReturn::class)->name('orders.return');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/addresses', [AddressController::class, 'store'])->name('addresses.store');
    Route::put('/addresses/{address}', [AddressController::class, 'update'])->name('addresses.update');
    Route::delete('/addresses/{address}', [AddressController::class, 'destroy'])->name('addresses.destroy');
});

// Reviews (requires auth)
Route::middleware('auth')->post('/reviews', SubmitReview::class)->name('reviews.store');

// Wishlist (requires auth)
Route::middleware('auth')->post('/wishlist/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');

// Back-in-stock alerts (no auth required)
Route::post('/stock-alerts', [StockAlertController::class, 'store'])->name('stock-alerts.store');

// Waitlist (no auth required)
Route::post('/waitlist', JoinWaitlist::class)->name('waitlist.join');

// Save for Later (auth required)
Route::middleware('auth')->group(function () {
    Route::post('/saved-items', [SavedItemController::class, 'store'])->name('saved-items.store');
    Route::delete('/saved-items/{savedItem}', [SavedItemController::class, 'destroy'])->name('saved-items.destroy');
});

// Notifications (auth required)
Route::middleware('auth')->group(function () {
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
});

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
