<?php

namespace App\Actions\Cart;

use App\Models\CartItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;

class RemoveFromCart
{
    use AsAction;

    public function handle(CartItem $item): void
    {
        $item->delete();
    }

    public function asController(Request $request, CartItem $item): RedirectResponse
    {
        $this->handle($item);

        return back()->with('success', 'Item removed from cart.');
    }
}
