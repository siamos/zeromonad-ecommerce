<?php

namespace App\Actions\Cart;

use App\Models\CartItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateCartItem
{
    use AsAction;

    public function handle(CartItem $item, int $quantity): CartItem
    {
        $item->update(['quantity' => max(1, $quantity)]);
        return $item->fresh();
    }

    public function asController(Request $request, CartItem $item): RedirectResponse
    {
        $request->validate(['quantity' => 'required|integer|min:1|max:99']);
        $this->handle($item, $request->integer('quantity'));
        return back();
    }
}
