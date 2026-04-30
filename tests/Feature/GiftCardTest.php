<?php

use App\Actions\Cart\AddToCart;
use App\Actions\Orders\CreateOrder;
use App\Models\Cart;
use App\Models\GiftCard;
use App\Models\Product;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;

// ─── RedeemGiftCard ───────────────────────────────────────────────────────────

it('applies a valid gift card to the cart', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 80]);
    $giftCard = GiftCard::factory()->create([
        'code' => 'GC-TEST-CARD',
        'original_amount' => 20,
        'remaining_balance' => 20,
        'is_active' => true,
        'expires_at' => null,
    ]);

    AddToCart::run($product, 1, [], $user->id);

    actingAs($user)
        ->post(route('cart.gift-card'), ['gift_card_code' => 'GC-TEST-CARD'])
        ->assertRedirect();

    assertDatabaseHas('carts', ['user_id' => $user->id, 'gift_card_id' => $giftCard->id]);
});

it('rejects a spent gift card', function () {
    $user = User::factory()->create();
    AddToCart::run(Product::factory()->create(), 1, [], $user->id);

    GiftCard::factory()->spent()->create(['code' => 'GC-SPENT-001']);

    actingAs($user)
        ->post(route('cart.gift-card'), ['gift_card_code' => 'GC-SPENT-001'])
        ->assertSessionHasErrors('gift_card_code');
});

it('rejects an expired gift card', function () {
    $user = User::factory()->create();
    AddToCart::run(Product::factory()->create(), 1, [], $user->id);

    GiftCard::factory()->expired()->create(['code' => 'GC-EXP-0001']);

    actingAs($user)
        ->post(route('cart.gift-card'), ['gift_card_code' => 'GC-EXP-0001'])
        ->assertSessionHasErrors('gift_card_code');
});

it('rejects an unknown gift card code', function () {
    $user = User::factory()->create();
    AddToCart::run(Product::factory()->create(), 1, [], $user->id);

    actingAs($user)
        ->post(route('cart.gift-card'), ['gift_card_code' => 'GC-FAKE-CODE'])
        ->assertSessionHasErrors('gift_card_code');
});

// ─── Gift card discount in CreateOrder ────────────────────────────────────────

it('deducts gift card balance when order is placed', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 80]);

    $giftCard = GiftCard::factory()->create([
        'original_amount' => 20,
        'remaining_balance' => 20,
        'expires_at' => null,
    ]);

    $cart = Cart::firstOrCreate(['user_id' => $user->id]);
    AddToCart::run($product, 1, [], $user->id);
    $cart->update(['gift_card_id' => $giftCard->id]);
    $cart->load('items', 'coupon', 'giftCard');

    $order = CreateOrder::run(
        cart: $cart,
        paymentMethod: 'cash',
        billingAddress: [
            'name' => 'Test', 'email' => 'test@example.com',
            'phone' => '123', 'line1' => 'Street 1',
            'city' => 'Athens', 'zip' => '10431',
        ],
    );

    expect((float) $order->total)->toBe(60.0);
    expect((float) $order->discount_amount)->toBe(20.0);

    $giftCard->refresh();
    expect((float) $giftCard->remaining_balance)->toBe(0.0);
    expect($giftCard->is_active)->toBeFalse();
});

it('caps gift card discount at order subtotal', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 10]);

    $giftCard = GiftCard::factory()->create([
        'original_amount' => 50,
        'remaining_balance' => 50,
        'expires_at' => null,
    ]);

    $cart = Cart::firstOrCreate(['user_id' => $user->id]);
    AddToCart::run($product, 1, [], $user->id);
    $cart->update(['gift_card_id' => $giftCard->id]);
    $cart->load('items', 'coupon', 'giftCard');

    $order = CreateOrder::run(
        cart: $cart,
        paymentMethod: 'cash',
        billingAddress: [
            'name' => 'Test', 'email' => 'test@example.com',
            'phone' => '123', 'line1' => 'Street 1',
            'city' => 'Athens', 'zip' => '10431',
        ],
    );

    expect((float) $order->total)->toBe(0.0);
    expect((float) $order->discount_amount)->toBe(10.0);

    $giftCard->refresh();
    expect((float) $giftCard->remaining_balance)->toBe(40.0);
    expect($giftCard->is_active)->toBeTrue();
});
