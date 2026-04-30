<?php

use App\Models\Product;
use App\Models\Review;
use App\Models\StockAlert;
use App\Models\User;
use App\Models\Wishlist;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\post;

// ─── Reviews ──────────────────────────────────────────────────────────────────

it('authenticated user can submit a product review', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    actingAs($user)
        ->post(route('reviews.store'), [
            'product_id' => $product->id,
            'rating' => 5,
            'title' => 'Great product',
            'body' => 'Really enjoyed using this product.',
        ])
        ->assertRedirect();

    assertDatabaseHas('reviews', [
        'user_id' => $user->id,
        'reviewable_type' => 'product',
        'reviewable_id' => $product->id,
        'rating' => 5,
        'status' => 'pending',
    ]);
});

it('review submission requires rating and body', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    actingAs($user)
        ->post(route('reviews.store'), [
            'product_id' => $product->id,
        ])
        ->assertSessionHasErrors(['rating', 'body']);
});

it('guest cannot submit a review', function () {
    $product = Product::factory()->create();

    post(route('reviews.store'), [
        'product_id' => $product->id,
        'rating' => 4,
        'body' => 'Nice.',
    ])->assertRedirect(route('login'));
});

it('updates existing review on resubmit', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    Review::create([
        'reviewable_type' => 'product',
        'reviewable_id' => $product->id,
        'product_id' => $product->id,
        'user_id' => $user->id,
        'rating' => 3,
        'body' => 'Okay.',
        'status' => 'approved',
    ]);

    actingAs($user)
        ->post(route('reviews.store'), [
            'product_id' => $product->id,
            'rating' => 5,
            'body' => 'Changed my mind — excellent!',
        ]);

    expect(Review::where('user_id', $user->id)->where('product_id', $product->id)->count())->toBe(1);
    assertDatabaseHas('reviews', ['user_id' => $user->id, 'rating' => 5, 'status' => 'pending']);
});

// ─── Wishlist ─────────────────────────────────────────────────────────────────

it('toggles a product onto the wishlist', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    actingAs($user)
        ->post(route('wishlist.toggle'), ['product_id' => $product->id])
        ->assertOk()
        ->assertJson(['wishlisted' => true]);

    assertDatabaseHas('wishlists', [
        'user_id' => $user->id,
        'wishable_type' => 'product',
        'wishable_id' => $product->id,
    ]);
});

it('removes a product from the wishlist on second toggle', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    Wishlist::create([
        'user_id' => $user->id,
        'wishable_type' => 'product',
        'wishable_id' => $product->id,
        'product_id' => $product->id,
    ]);

    actingAs($user)
        ->post(route('wishlist.toggle'), ['product_id' => $product->id])
        ->assertJson(['wishlisted' => false]);

    assertDatabaseMissing('wishlists', [
        'user_id' => $user->id,
        'wishable_id' => $product->id,
    ]);
});

it('guest cannot toggle wishlist', function () {
    $product = Product::factory()->create();

    post(route('wishlist.toggle'), ['product_id' => $product->id])
        ->assertRedirect(route('login'));
});

// ─── Stock Alerts ─────────────────────────────────────────────────────────────

it('registers a stock alert for an out-of-stock product', function () {
    $product = Product::factory()->outOfStock()->create();

    post(route('stock-alerts.store'), [
        'product_id' => $product->id,
        'email' => 'alert@example.com',
    ])->assertOk()->assertJson(['success' => true]);

    assertDatabaseHas('stock_alerts', [
        'product_id' => $product->id,
        'email' => 'alert@example.com',
    ]);
});

it('does not duplicate stock alerts', function () {
    $product = Product::factory()->create();

    post(route('stock-alerts.store'), ['product_id' => $product->id, 'email' => 'dup@example.com']);
    post(route('stock-alerts.store'), ['product_id' => $product->id, 'email' => 'dup@example.com']);

    expect(StockAlert::where('product_id', $product->id)->where('email', 'dup@example.com')->count())->toBe(1);
});

it('requires a valid email for stock alerts', function () {
    $product = Product::factory()->create();

    post(route('stock-alerts.store'), [
        'product_id' => $product->id,
        'email' => 'not-an-email',
    ], ['Accept' => 'application/json'])->assertStatus(422)->assertJsonValidationErrors('email');
});
