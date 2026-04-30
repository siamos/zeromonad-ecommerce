<?php

use App\Actions\Cart\AddToCart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;

$billingAddress = [
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'phone' => '+306900000000',
    'line1' => '123 Main St',
    'city' => 'Athens',
    'zip' => '10431',
    'country' => 'GR',
];

// ─── Checkout ─────────────────────────────────────────────────────────────────

it('creates an order for authenticated user with cash payment', function () use ($billingAddress) {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 50, 'stock' => 10]);

    AddToCart::run($product, 2, [], $user->id);

    actingAs($user)
        ->post(route('checkout.process'), [
            'payment_method' => 'cash',
            'billing_address' => $billingAddress,
        ])
        ->assertRedirect();

    $order = Order::where('user_id', $user->id)->first();

    expect($order)->not->toBeNull();
    expect((float) $order->subtotal)->toBe(100.0);
    expect($order->items)->toHaveCount(1);
});

it('clears the cart after checkout', function () use ($billingAddress) {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 30, 'stock' => 5]);

    AddToCart::run($product, 1, [], $user->id);

    actingAs($user)
        ->post(route('checkout.process'), [
            'payment_method' => 'cash',
            'billing_address' => $billingAddress,
        ]);

    assertDatabaseHas('carts', ['user_id' => $user->id]);
    expect(CartItem::whereHas('cart', fn ($q) => $q->where('user_id', $user->id))->count())->toBe(0);
});

it('rejects checkout with empty cart', function () use ($billingAddress) {
    $user = User::factory()->create();

    actingAs($user)
        ->post(route('checkout.process'), [
            'payment_method' => 'cash',
            'billing_address' => $billingAddress,
        ])
        ->assertStatus(404);
});

it('requires billing address fields', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 10]);

    AddToCart::run($product, 1, [], $user->id);

    actingAs($user)
        ->post(route('checkout.process'), [
            'payment_method' => 'cash',
            'billing_address' => [],
        ])
        ->assertSessionHasErrors(['billing_address.name', 'billing_address.email']);
});

it('awards loyalty points to user after paid order', function () use ($billingAddress) {
    $user = User::factory()->create(['points_balance' => 0]);
    $product = Product::factory()->create(['price' => 100, 'stock' => 5]);

    AddToCart::run($product, 1, [], $user->id);

    actingAs($user)
        ->post(route('checkout.process'), [
            'payment_method' => 'cash',
            'billing_address' => $billingAddress,
        ]);

    $user->refresh();
    expect($user->points_balance)->toBeGreaterThan(0);
});
