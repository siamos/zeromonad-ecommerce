<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Ticket;
use App\Models\Wishlist;
use App\Settings\GeneralSettings;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();
        $theme = app(GeneralSettings::class)->active_theme;

        if ($theme !== 'Products') {
            $orders = Order::with('items')
                ->where('user_id', $user->id)
                ->latest()
                ->paginate(15);

            return Inertia::render('Account', [
                'user' => $user->only('id', 'name', 'email', 'points_balance', 'referral_code'),
                'orders' => $orders,
                'addresses' => $user->addresses()->orderByDesc('is_default')->get(),
            ]);
        }

        $wishlistItems = Wishlist::with(['product' => fn ($q) => $q->with('media')])
            ->where('user_id', $user->id)
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

        $pointsHistory = $user->pointsTransactions()->latest()->take(10)->get();

        return Inertia::render('Account', [
            'user' => $user->only('id', 'name', 'email', 'points_balance', 'referral_code'),
            'wishlistItems' => $wishlistItems,
            'pointsHistory' => $pointsHistory,
            'addresses' => $user->addresses()->orderByDesc('is_default')->get(),
        ]);
    }

    public function orders(): Response
    {
        $orders = Order::with('items')
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(15);

        return Inertia::render('Orders', [
            'user' => auth()->user()->only('id', 'name', 'email'),
            'orders' => $orders,
        ]);
    }

    public function show(Order $order): Response
    {
        abort_unless($order->user_id === auth()->id(), 403);

        return Inertia::render('Order/Show', [
            'order' => $order->load('items'),
            'tickets' => $order->tickets()->with('orderItem')->get()->map(fn (Ticket $t) => [
                'id' => $t->id,
                'ticket_code' => $t->ticket_code,
                'order_item_id' => $t->order_item_id,
                'attendee_name' => $t->attendee_name,
                'used_at' => $t->used_at,
                'download_url' => route('account.orders.ticket', ['order' => $order->id, 'ticket' => $t->ticket_code]),
            ]),
        ]);
    }

    public function invoice(Order $order): HttpResponse
    {
        abort_unless($order->user_id === auth()->id(), 403);

        $settings = app(GeneralSettings::class);

        $pdf = Pdf::loadView('pdf.invoice', [
            'order' => $order->load('items'),
            'siteName' => $settings->site_name ?? config('app.name'),
            'currency' => $settings->currency ?? 'EUR',
        ]);

        return $pdf->download("invoice-{$order->order_number}.pdf");
    }

    public function ticket(Order $order, string $ticket): HttpResponse
    {
        abort_unless($order->user_id === auth()->id(), 403);

        $ticketModel = Ticket::where('order_id', $order->id)
            ->where('ticket_code', $ticket)
            ->with('orderItem')
            ->firstOrFail();

        $settings = app(GeneralSettings::class);

        $pdf = Pdf::loadView('pdf.ticket', [
            'ticket' => $ticketModel,
            'order' => $order,
            'siteName' => $settings->site_name ?? config('app.name'),
        ])->setPaper([0, 0, 595, 280]);

        return $pdf->download("ticket-{$ticketModel->ticket_code}.pdf");
    }
}
