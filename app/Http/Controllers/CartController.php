<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public function index(): Response
    {
        $cart = $this->getCart();

        return Inertia::render('Cart', [
            'cart' => $cart?->load(['items.product.activityDetail', 'coupon']),
        ]);
    }

    private function getCart(): ?Cart
    {
        if (auth()->check()) {
            return Cart::with('items.product')->firstOrCreate(
                ['user_id' => auth()->id()],
                ['session_id' => session()->getId()]
            );
        }

        return Cart::with('items.product')
            ->where('session_id', session()->getId())
            ->whereNull('user_id')
            ->first();
    }
}
