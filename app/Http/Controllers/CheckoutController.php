<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\PaymentGateways\PaymentGatewayManager;
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

        return Inertia::render('Checkout', [
            'cart' => $cart,
            'paymentMethods' => $gateways->available(),
            'bankAccounts' => collect($paymentSettings->bank_accounts)
                ->filter(fn ($b) => ! empty($b['iban']))
                ->values(),
            'user' => auth()->user()?->only('name', 'email'),
            'pointsBalance' => auth()->user()?->points_balance ?? 0,
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
