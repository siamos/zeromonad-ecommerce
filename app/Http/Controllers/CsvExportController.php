<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvExportController extends Controller
{
    public function orders(Request $request): StreamedResponse
    {
        abort_unless(
            auth()->user()?->hasAnyRole(['admin', 'manager']),
            403
        );

        $filename = 'orders-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($request) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Order Number', 'Customer', 'Email', 'Phone',
                'Status', 'Payment Status', 'Payment Method',
                'Subtotal', 'Discount', 'Tax', 'Total',
                'Items', 'Created At',
            ]);

            Order::with(['user', 'items'])
                ->when(
                    $request->get('status'),
                    fn ($q, $status) => $q->where('status', $status)
                )
                ->orderByDesc('created_at')
                ->chunk(500, function ($orders) use ($handle) {
                    foreach ($orders as $order) {
                        $items = $order->items
                            ->map(fn ($i) => "{$i->product_name} x{$i->quantity}")
                            ->implode('; ');

                        fputcsv($handle, [
                            $order->order_number,
                            $order->billing_address['name'] ?? $order->user?->name ?? '',
                            $order->billing_address['email'] ?? $order->user?->email ?? '',
                            $order->billing_address['phone'] ?? '',
                            $order->status,
                            $order->payment_status,
                            $order->payment_method,
                            number_format((float) $order->subtotal, 2),
                            number_format((float) $order->discount_amount, 2),
                            number_format((float) $order->tax_amount, 2),
                            number_format((float) $order->total, 2),
                            $items,
                            $order->created_at->format('Y-m-d H:i'),
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Cache-Control' => 'no-cache',
        ]);
    }
}
