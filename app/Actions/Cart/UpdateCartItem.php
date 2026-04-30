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
        $quantity = max(1, $quantity);
        $updates = ['quantity' => $quantity];

        $bookable = $item->cartable;

        if ($bookable && method_exists($bookable, 'priceTiers') && $bookable->priceTiers->isNotEmpty()) {
            $tier = $bookable->priceTiers
                ->filter(fn ($t) => $t->min_quantity <= $quantity)
                ->sortByDesc('min_quantity')
                ->first();

            if ($tier) {
                $updates['unit_price'] = (float) $tier->price;
            }
        }

        $item->update($updates);

        return $item->fresh();
    }

    public function asController(Request $request, CartItem $item): RedirectResponse
    {
        $request->validate(['quantity' => 'required|integer|min:1|max:99']);
        $this->handle($item, $request->integer('quantity'));

        return back();
    }
}
