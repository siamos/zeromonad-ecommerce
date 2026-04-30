<?php

namespace App\Actions\GiftCards;

use App\Models\Cart;
use App\Models\GiftCard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

class RedeemGiftCard
{
    use AsAction;

    public function handle(Cart $cart, string $code): GiftCard
    {
        $giftCard = GiftCard::where('code', strtoupper($code))->first();

        if (! $giftCard || ! $giftCard->isUsable()) {
            throw ValidationException::withMessages(['gift_card_code' => 'This gift card is invalid, expired, or has no remaining balance.']);
        }

        $cart->update(['gift_card_id' => $giftCard->id]);

        return $giftCard;
    }

    public function asController(Request $request): RedirectResponse
    {
        $request->validate(['gift_card_code' => 'required|string']);

        $cart = auth()->check()
            ? Cart::firstOrCreate(['user_id' => auth()->id()])
            : Cart::firstOrCreate(['session_id' => $request->session()->getId()]);

        $giftCard = $this->handle($cart, $request->gift_card_code);

        return back()->with('success', "Gift card applied! €{$giftCard->remaining_balance} balance available.");
    }
}
