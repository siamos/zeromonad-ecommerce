<?php

namespace App\Actions\Cart;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;

class AddToCart
{
    use AsAction;

    public function handle(Product $product, int $quantity = 1, array $options = [], ?int $userId = null, ?string $sessionId = null): CartItem
    {
        $cart = Cart::firstOrCreate(
            $userId
                ? ['user_id' => $userId]
                : ['session_id' => $sessionId],
        );

        $item = $cart->items()->where('product_id', $product->id)->first();

        if ($item) {
            $item->increment('quantity', $quantity);
        } else {
            $item = $cart->items()->create([
                'product_id' => $product->id,
                'quantity'   => $quantity,
                'unit_price' => $product->price,
                'options'    => $options,
            ]);
        }

        return $item->fresh();
    }

    public function asController(Request $request): RedirectResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'integer|min:1|max:99',
        ]);

        $product  = Product::findOrFail($request->product_id);
        $quantity = $request->integer('quantity', 1);

        $this->handle(
            product: $product,
            quantity: $quantity,
            userId: $request->user()?->id,
            sessionId: $request->session()->getId(),
        );

        return back()->with('success', "Added {$product->name} to cart.");
    }
}
