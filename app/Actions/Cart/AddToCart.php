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
                'quantity' => $quantity,
                'unit_price' => $product->price,
                'options' => $options,
            ]);
        }

        return $item->fresh();
    }

    public function asController(Request $request): RedirectResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'integer|min:1|max:99',
            'booking_date' => 'nullable|date',
            'check_in' => 'nullable|date',
            'check_out' => 'nullable|date|after:check_in',
            'pickup_date' => 'nullable|date',
            'return_date' => 'nullable|date|after:pickup_date',
            'guests' => 'nullable|integer|min:1',
            'slot_id' => 'nullable|exists:activity_slots,id',
            'extras' => 'nullable|array',
            'extras.*' => 'string',
        ]);

        $product = Product::findOrFail($request->product_id);
        $quantity = $request->integer('quantity', 1);

        $options = array_filter([
            'booking_date' => $request->booking_date,
            'check_in' => $request->check_in,
            'check_out' => $request->check_out,
            'pickup_date' => $request->pickup_date,
            'return_date' => $request->return_date,
            'guests' => $request->guests,
            'slot_id' => $request->slot_id,
            'extras' => $request->extras ?: null,
        ]);

        $this->handle(
            product: $product,
            quantity: $quantity,
            options: $options,
            userId: $request->user()?->id,
            sessionId: $request->session()->getId(),
        );

        return back()->with('success', "Added {$product->name} to cart.");
    }
}
