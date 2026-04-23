<?php

namespace App\Actions\Orders;

use App\Actions\Cart\ClearCart;
use App\Http\Requests\CheckoutRequest;
use App\Mail\OrderConfirmation;
use App\Models\ActivitySlot;
use App\Models\Cart;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateOrder
{
    use AsAction;

    public function handle(Cart $cart, string $paymentMethod, array $billingAddress, array $shippingAddress = [], int $usePoints = 0): Order
    {
        return DB::transaction(function () use ($cart, $paymentMethod, $billingAddress, $shippingAddress, $usePoints) {
            $subtotal = $cart->subtotal();
            $discount = $cart->coupon?->calculateDiscount($subtotal) ?? 0;

            $user = $cart->user_id ? User::find($cart->user_id) : null;
            $pointsDiscount = 0;
            if ($user && $usePoints > 0) {
                $pointsToUse = min($usePoints, $user->points_balance);
                $pointsDiscount = $pointsToUse / 100;
            }

            $total = max(0, $subtotal - $discount - $pointsDiscount);

            $order = Order::create([
                'user_id' => $cart->user_id,
                'guest_token' => $cart->user_id ? null : Str::uuid(),
                'coupon_id' => $cart->coupon_id,
                'subtotal' => $subtotal,
                'discount_amount' => $discount + $pointsDiscount,
                'total' => $total,
                'payment_method' => $paymentMethod,
                'payment_status' => 'unpaid',
                'billing_address' => $billingAddress,
                'shipping_address' => $shippingAddress ?: $billingAddress,
            ]);

            foreach ($cart->items()->with('product')->get() as $item) {
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'product_sku' => $item->product->sku,
                    'unit_price' => $item->unit_price,
                    'quantity' => $item->quantity,
                    'subtotal' => $item->lineTotal(),
                    'options' => $item->options,
                ]);

                if (! empty($item->options['slot_id'])) {
                    ActivitySlot::where('id', $item->options['slot_id'])
                        ->increment('booked_count', $item->quantity);
                }
            }

            if ($cart->coupon_id) {
                $cart->coupon->increment('uses_count');
            }

            ClearCart::run($cart);

            if ($user) {
                if ($pointsDiscount > 0) {
                    $pointsUsed = (int) ($pointsDiscount * 100);
                    $user->redeemPoints($pointsUsed, "Redeemed on order {$order->order_number}", $order);
                }

                $pointsEarned = (int) floor($total);
                if ($pointsEarned > 0) {
                    $user->awardPoints($pointsEarned, "Earned from order {$order->order_number}", $order);
                }
            }

            return $order;
        });
    }

    public function asController(CheckoutRequest $request): RedirectResponse
    {
        $cart = auth()->check()
            ? Cart::where('user_id', auth()->id())->with(['items.product', 'coupon'])->firstOrFail()
            : Cart::where('session_id', $request->session()->getId())->with(['items.product', 'coupon'])->firstOrFail();

        if ($cart->items->isEmpty()) {
            return back()->with('error', 'Your cart is empty.');
        }

        $order = $this->handle(
            cart: $cart,
            paymentMethod: $request->payment_method,
            billingAddress: $request->billing_address,
            shippingAddress: $request->shipping_address ?? [],
            usePoints: $request->integer('use_points', 0),
        );

        if ($order->guest_token) {
            $request->session()->put('guest_order_token', $order->guest_token);
        }

        Mail::send(new OrderConfirmation($order));

        return redirect()->route('checkout.callback', $order->payment_method)
            ->with('order_id', $order->id);
    }
}
