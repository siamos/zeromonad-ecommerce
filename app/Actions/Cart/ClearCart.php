<?php

namespace App\Actions\Cart;

use App\Models\Cart;
use Lorisleiva\Actions\Concerns\AsAction;

class ClearCart
{
    use AsAction;

    public function handle(Cart $cart): void
    {
        $cart->items()->delete();
        $cart->update(['coupon_id' => null]);
    }
}
