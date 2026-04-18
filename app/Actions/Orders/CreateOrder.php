<?php

namespace App\Actions\Orders;

use App\Actions\Cart\ClearCart;
use App\Http\Requests\CheckoutRequest;
use App\Mail\OrderConfirmation;
use App\Models\Cart;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateOrder
{
    use AsAction;

    public function handle(Cart $cart, string $paymentMethod, array $billingAddress, array $shippingAddress = []): Order
    {
        return DB::transaction(function () use ($cart, $paymentMethod, $billingAddress, $shippingAddress) {
            $subtotal = $cart->subtotal();
            $discount = $cart->coupon?->calculateDiscount($subtotal) ?? 0;
            $total    = max(0, $subtotal - $discount);

            $order = Order::create([
                'user_id'          => $cart->user_id,
                'coupon_id'        => $cart->coupon_id,
                'subtotal'         => $subtotal,
                'discount_amount'  => $discount,
                'total'            => $total,
                'payment_method'   => $paymentMethod,
                'payment_status'   => 'unpaid',
                'billing_address'  => $billingAddress,
                'shipping_address' => $shippingAddress ?: $billingAddress,
            ]);

            foreach ($cart->items()->with('product')->get() as $item) {
                $order->items()->create([
                    'product_id'   => $item->product_id,
                    'product_name' => $item->product->name,
                    'product_sku'  => $item->product->sku,
                    'unit_price'   => $item->unit_price,
                    'quantity'     => $item->quantity,
                    'subtotal'     => $item->lineTotal(),
                    'options'      => $item->options,
                ]);
            }

            if ($cart->coupon_id) {
                $cart->coupon->increment('uses_count');
            }

            ClearCart::run($cart);

            return $order;
        });
    }

    public function asController(CheckoutRequest $request): RedirectResponse
    {
        $cart = Cart::where('user_id', $request->user()->id)
            ->with(['items.product', 'coupon'])
            ->firstOrFail();

        if ($cart->items->isEmpty()) {
            return back()->with('error', 'Your cart is empty.');
        }

        $order = $this->handle(
            cart: $cart,
            paymentMethod: $request->payment_method,
            billingAddress: $request->billing_address,
            shippingAddress: $request->shipping_address ?? [],
        );

        Mail::send(new OrderConfirmation($order));

        return redirect()->route('checkout.callback', $order->payment_method)
            ->with('order_id', $order->id);
    }
}
