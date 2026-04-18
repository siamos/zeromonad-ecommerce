<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(): Response
    {
        $orders = Order::with('items')
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return Inertia::render('Account', [
            'user'   => auth()->user()->only('id', 'name', 'email'),
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
