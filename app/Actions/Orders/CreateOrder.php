<?php

namespace App\Actions\Orders;

use App\Actions\Cart\ClearCart;
use App\Actions\Payments\ProcessPayment;
use App\Http\Requests\CheckoutRequest;
use App\Mail\OrderConfirmation;
use App\Models\ActivitySlot;
use App\Models\Cart;
use App\Models\GiftCard;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Vehicle;
use App\PaymentGateways\PaymentGatewayManager;
use Illuminate\Database\Eloquent\Model;
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
            $subtotal = $cart->subtotal;
            $couponDiscount = $cart->coupon?->calculateDiscount($subtotal) ?? 0;

            $giftCard = $cart->gift_card_id ? GiftCard::find($cart->gift_card_id) : null;
            $giftCardDiscount = ($giftCard && $giftCard->isUsable())
                ? (float) min($giftCard->remaining_balance, $subtotal - $couponDiscount)
                : 0;

            $discount = $couponDiscount + $giftCardDiscount;

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

            foreach ($cart->items()->with(['product', 'cartable'])->get() as $item) {
                $bookable = $item->cartable ?? $item->product;

                $order->items()->create([
                    'product_id' => $item->product_id,
                    'orderable_type' => $item->cartable_type,
                    'orderable_id' => $item->cartable_id,
                    'product_name' => $this->resolveItemName($bookable),
                    'product_sku' => $bookable instanceof Product ? $bookable->sku : null,
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

            if ($giftCard && $giftCardDiscount > 0) {
                $newBalance = $giftCard->remaining_balance - $giftCardDiscount;
                $giftCard->update([
                    'remaining_balance' => $newBalance,
                    'is_active' => $newBalance > 0,
                    'redeemed_by_user_id' => $user?->id,
                ]);
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

    private function resolveItemName(?Model $bookable): string
    {
        if (! $bookable) {
            return '';
        }

        if ($bookable instanceof Vehicle) {
            return "{$bookable->make} {$bookable->model} {$bookable->year}";
        }

        if ($bookable instanceof Product) {
            $name = $bookable->name;

            return is_array($name) ? ($name['en'] ?? '') : (string) $name;
        }

        $title = $bookable->title ?? '';

        return is_array($title) ? ($title['en'] ?? '') : (string) $title;
    }

    public function asController(CheckoutRequest $request): RedirectResponse
    {
        $cart = auth()->check()
            ? Cart::where('user_id', auth()->id())->with(['items.product', 'items.cartable', 'coupon', 'giftCard'])->firstOrFail()
            : Cart::where('session_id', $request->session()->getId())->with(['items.product', 'items.cartable', 'coupon', 'giftCard'])->firstOrFail();

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

        $gateway = app(PaymentGatewayManager::class)->resolve($order->payment_method);

        if (! $gateway->requiresRedirect()) {
            ProcessPayment::run($order);

            return redirect()->route('checkout.success', $order);
        }

        return redirect()->route('checkout.callback', $order->payment_method)
            ->with('order_id', $order->id);
    }
}
