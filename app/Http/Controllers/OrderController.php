<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Wishlist;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(): Response
    {
        $wishlistItems = Wishlist::with(['product' => fn ($q) => $q->with('media')])
            ->where('user_id', auth()->id())
            ->latest()
            ->get()
            ->map(fn (Wishlist $w) => [
                'id' => $w->product->id,
                'name' => $w->product->name,
                'slug' => $w->product->slug,
                'price' => (float) $w->product->price,
                'image_url' => $w->product->image_url,
                'short_description' => $w->product->short_description,
            ]);

        $user = auth()->user();
        $pointsHistory = $user->pointsTransactions()->latest()->take(10)->get();

        return Inertia::render('Account', [
            'user' => $user->only('id', 'name', 'email', 'points_balance'),
            'wishlistItems' => $wishlistItems,
            'pointsHistory' => $pointsHistory,
        ]);
    }

    public function orders(): Response
    {
        $orders = Order::with('items')
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(15);

        $user = auth()->user();

        return Inertia::render('Orders', [
            'user' => $user->only('id', 'name', 'email'),
            'orders' => $orders,
        ]);
    }

    public function show(Order $order): Response
    {
        abort_unless($order->user_id === auth()->id(), 403);

        return Inertia::render('Order/Show', [
            'order' => $order->load('items'),
        ]);
    }
}
