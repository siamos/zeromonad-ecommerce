<?php

namespace App\Http\Controllers;

use App\Models\Accommodation;
use App\Models\Activity;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\Vehicle;
use App\PaymentGateways\PaymentGatewayManager;
use App\Settings\GeneralSettings;
use App\Settings\PaymentSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function index(Request $request, PaymentGatewayManager $gateways, PaymentSettings $paymentSettings): Response|RedirectResponse
    {
        $cart = auth()->check()
            ? Cart::with(['items.product.activityDetail', 'coupon'])->where('user_id', auth()->id())->first()
            : Cart::with(['items.product.activityDetail', 'coupon'])->where('session_id', $request->session()->getId())->first();

        if (! $cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index');
        }

        $theme = app(GeneralSettings::class)->active_theme;
        $cartIds = $cart->items->pluck('product_id')->all();

        $upsells = match ($theme) {
            'Activities' => Activity::where('status', 'active')
                ->where('featured', true)
                ->whereNotIn('id', $cartIds)
                ->with('media')
                ->limit(3)
                ->get()
                ->map(fn (Activity $a) => [
                    'id' => $a->id,
                    'name' => $a->getTranslation('title', 'en'),
                    'price' => (float) $a->price,
                    'image_url' => $a->image_url,
                    'slug' => $a->slug,
                ]),
            'Bookings' => Accommodation::where('status', 'active')
                ->where('featured', true)
                ->whereNotIn('id', $cartIds)
                ->with('media')
                ->limit(3)
                ->get()
                ->map(fn (Accommodation $a) => [
                    'id' => $a->id,
                    'name' => $a->getTranslation('title', 'en'),
                    'price' => (float) $a->price_per_night,
                    'image_url' => $a->image_url,
                    'slug' => $a->slug,
                ]),
            'Cars' => Vehicle::where('status', 'available')
                ->where('featured', true)
                ->whereNotIn('id', $cartIds)
                ->with('media')
                ->limit(3)
                ->get()
                ->map(fn (Vehicle $v) => [
                    'id' => $v->id,
                    'name' => "{$v->year} {$v->make} {$v->model}",
                    'price' => (float) $v->price_per_day,
                    'image_url' => $v->image_url,
                    'slug' => $v->slug,
                ]),
            default => Product::where('status', 'published')
                ->where('featured', true)
                ->whereNotIn('id', $cartIds)
                ->with('media')
                ->limit(3)
                ->get()
                ->map(fn (Product $p) => [
                    'id' => $p->id,
                    'name' => $p->getTranslation('name', 'en'),
                    'price' => (float) $p->price,
                    'image_url' => $p->image_url,
                    'slug' => $p->slug,
                ]),
        };

        return Inertia::render('Checkout', [
            'cart' => $cart,
            'paymentMethods' => $gateways->available(),
            'bankAccounts' => collect($paymentSettings->bank_accounts)
                ->filter(fn ($b) => ! empty($b['iban']))
                ->values(),
            'user' => auth()->user()?->only('name', 'email'),
            'pointsBalance' => auth()->user()?->points_balance ?? 0,
            'upsells' => $upsells->values(),
        ]);
    }

    public function success(Request $request, Order $order): Response
    {
        if ($order->user_id) {
            abort_unless($order->user_id === auth()->id(), 403);
        } else {
            abort_unless($request->session()->get('guest_order_token') === $order->guest_token, 403);
        }

        return Inertia::render('OrderSuccess', [
            'order' => $order->load('items'),
            'isGuest' => is_null($order->user_id),
        ]);
    }
}
