<?php

use App\Actions\Cart\AddToCart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\post;

// ─── AddToCart ────────────────────────────────────────────────────────────────

it('adds a product to a guest cart via session', function () {
    $product = Product::factory()->create(['price' => 29.99, 'stock' => 10]);

    post(route('cart.add'), [
        'product_id' => $product->id,
        'quantity' => 2,
    ])->assertRedirect();

    assertDatabaseHas('cart_items', [
        'product_id' => $product->id,
        'quantity' => 2,
    ]);
});

it('adds a product to an authenticated user cart', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock' => 5]);

    actingAs($user)
        ->post(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertRedirect();

    assertDatabaseHas('cart_items', ['product_id' => $product->id, 'quantity' => 1]);
    assertDatabaseHas('carts', ['user_id' => $user->id]);
});

it('increments quantity when adding the same product twice', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock' => 20]);

    actingAs($user)->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1]);
    actingAs($user)->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 2]);

    expect(
        CartItem::where('product_id', $product->id)->value('quantity')
    )->toBe(3);
});

// ─── UpdateCartItem ───────────────────────────────────────────────────────────

it('updates cart item quantity', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    $item = AddToCart::run($product, 1, [], $user->id);

    actingAs($user)
        ->patch(route('cart.update', $item->id), ['quantity' => 5])
        ->assertRedirect();

    assertDatabaseHas('cart_items', ['id' => $item->id, 'quantity' => 5]);
});

// ─── RemoveFromCart ───────────────────────────────────────────────────────────

it('removes a cart item', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    $item = AddToCart::run($product, 1, [], $user->id);

    actingAs($user)
        ->delete(route('cart.remove', $item->id))
        ->assertRedirect();

    assertDatabaseMissing('cart_items', ['id' => $item->id]);
});

// ─── ApplyCoupon ──────────────────────────────────────────────────────────────

it('applies a valid coupon to the cart', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 100]);

    AddToCart::run($product, 1, [], $user->id);

    $coupon = Coupon::create([
        'code' => 'SAVE10',
        'type' => 'percentage',
        'value' => 10,
        'active' => true,
    ]);

    actingAs($user)
        ->post(route('cart.coupon'), ['code' => 'SAVE10'])
        ->assertRedirect();

    assertDatabaseHas('carts', ['user_id' => $user->id, 'coupon_id' => $coupon->id]);
});

it('rejects an invalid coupon code', function () {
    $user = User::factory()->create();
    AddToCart::run(Product::factory()->create(), 1, [], $user->id);

    actingAs($user)
        ->post(route('cart.coupon'), ['code' => 'INVALID'])
        ->assertSessionHasErrors('code');
});

it('rejects an expired coupon', function () {
    $user = User::factory()->create();
    AddToCart::run(Product::factory()->create(['price' => 100]), 1, [], $user->id);

    Coupon::create([
        'code' => 'EXPIRED',
        'type' => 'fixed',
        'value' => 5,
        'active' => true,
        'expires_at' => now()->subDay(),
    ]);

    actingAs($user)
        ->post(route('cart.coupon'), ['code' => 'EXPIRED'])
        ->assertSessionHasErrors('code');
});
