@php
$statusColors = [
    'pending'    => '#d97706',
    'processing' => '#2563eb',
    'paid'       => '#059669',
    'shipped'    => '#7c3aed',
    'delivered'  => '#16a34a',
    'cancelled'  => '#dc2626',
    'refunded'   => '#6b7280',
];
$statusColor = $statusColors[$order->status] ?? '#4f46e5';
$statusLabel = ucfirst(str_replace('_', ' ', $order->status));
@endphp
<x-mail::message>
# Order Update

Hi {{ $order->billing_address['name'] ?? $order->user?->name ?? 'there' }},

Your order **{{ $order->order_number }}** has been updated.

<table style="width:100%; margin: 20px 0;">
<tr>
  <td style="padding: 20px; background-color: #f5f5ff; border-radius: 8px; text-align: center; border: 1px solid #e0e7ff;">
    <p style="margin:0; font-size:12px; color:#6b7280; text-transform:uppercase; letter-spacing:0.05em;">New Status</p>
    <p style="margin:8px 0 0; font-size:24px; font-weight:bold; color:{{ $statusColor }};">{{ $statusLabel }}</p>
  </td>
</tr>
</table>

@if ($order->status === 'processing')
<x-mail::panel>
**We're working on it!**

Your order is being prepared. We'll notify you again as soon as it ships.
</x-mail::panel>
@elseif ($order->status === 'paid')
<x-mail::panel>
**Payment confirmed.**

Your payment has been received and your order is being processed.
</x-mail::panel>
@elseif ($order->status === 'shipped')
<x-mail::panel>
**Your order is on its way!**

Your order has been dispatched. Tracking information will be shared as soon as it's available.
</x-mail::panel>
@elseif ($order->status === 'delivered')
<x-mail::panel>
**Delivered — enjoy!**

Your order has been delivered. We hope you love it! If anything isn't right, reply to this email.
</x-mail::panel>
@elseif ($order->status === 'cancelled')
<x-mail::panel>
**Order cancelled.**

Your order has been cancelled. If you were charged, a refund will be processed within 5–10 business days.
</x-mail::panel>
@elseif ($order->status === 'refunded')
<x-mail::panel>
**Refund issued.**

A refund of **{{ number_format($order->total, 2) }} {{ $currency }}** has been issued. Please allow 5–10 business days for it to appear on your statement.
</x-mail::panel>
@endif

| | |
|:--|:--|
| **Order** | {{ $order->order_number }} |
| **Previous Status** | {{ ucfirst(str_replace('_', ' ', $previousStatus)) }} |
| **Order Total** | {{ number_format($order->total, 2) }} {{ $currency }} |
| **Date** | {{ $order->created_at->format('d M Y') }} |

<x-mail::button :url="url('/account/orders/' . $order->id)">
View Order Details
</x-mail::button>

Thanks,<br>
{{ $siteName }}
</x-mail::message>
