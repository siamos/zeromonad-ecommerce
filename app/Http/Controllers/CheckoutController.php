<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\PaymentGateways\PaymentGatewayManager;
use App\Settings\PaymentSettings;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function index(PaymentGatewayManager $gateways, PaymentSettings $paymentSettings): Response
    {
        $cart = Cart::with(['items.product.activityDetail', 'coupon'])
            ->where('user_id', auth()->id())
            ->first();

        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index');
        }

        return Inertia::render('Checkout', [
            'cart'           => $cart,
            'paymentMethods' => $gateways->available(),
            'bankAccounts'   => collect($paymentSettings->bank_accounts)
                ->filter(fn ($b) => !empty($b['iban']))
                ->values(),
            'user'           => auth()->user()?->only('name', 'email'),
        ]);
    }

    public function success(Order $order): Response
    {
        abort_unless($order->user_id === auth()->id(), 403);

        return Inertia::render('OrderSuccess', [
            'order' => $order->load('items'),
        ]);
    }
}
