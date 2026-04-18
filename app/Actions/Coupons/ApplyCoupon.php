<?php

namespace App\Actions\Coupons;

use App\Models\Cart;
use App\Models\Coupon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use Illuminate\Validation\ValidationException;

class ApplyCoupon
{
    use AsAction;

    public function handle(Cart $cart, string $code): Coupon
    {
        $coupon = Coupon::where('code', strtoupper($code))->first();

        if (! $coupon || ! $coupon->isValid()) {
            throw ValidationException::withMessages(['code' => 'This coupon is invalid or expired.']);
        }

        if ($coupon->min_order_amount && $cart->subtotal() < $coupon->min_order_amount) {
            throw ValidationException::withMessages([
                'code' => "Minimum order amount of {$coupon->min_order_amount} required.",
            ]);
        }

        $cart->update(['coupon_id' => $coupon->id]);

        return $coupon;
    }

    public function asController(Request $request): RedirectResponse
    {
        $request->validate(['code' => 'required|string']);

        $cart = Cart::firstOrCreate(
            $request->user()
                ? ['user_id' => $request->user()->id]
                : ['session_id' => $request->session()->getId()]
        );

        $coupon = $this->handle($cart, $request->code);

        return back()->with('success', "Coupon {$coupon->code} applied!");
    }
}
