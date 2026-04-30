@php
$statusColors = [
    'approved' => '#16a34a',
    'rejected' => '#dc2626',
    'refunded' => '#7c3aed',
];
$statusColor = $statusColors[$returnRequest->status] ?? '#4f46e5';
$statusLabel = ucfirst($returnRequest->status);
@endphp
<x-mail::message>
# Return Request Update

Hi {{ $returnRequest->user->name }},

Your return request for order **{{ $order->order_number }}** has been updated.

<table style="width:100%; margin: 20px 0;">
<tr>
  <td style="padding: 20px; background-color: #f5f5ff; border-radius: 8px; text-align: center; border: 1px solid #e0e7ff;">
    <p style="margin:0; font-size:12px; color:#6b7280; text-transform:uppercase; letter-spacing:0.05em;">Status</p>
    <p style="margin:8px 0 0; font-size:24px; font-weight:bold; color:{{ $statusColor }};">{{ $statusLabel }}</p>
  </td>
</tr>
</table>

@if ($returnRequest->status === 'approved')
<x-mail::panel>
**Return approved!**

Please ship the item(s) back using the instructions provided by our team. Once received, we'll process your refund promptly.
</x-mail::panel>
@elseif ($returnRequest->status === 'rejected')
<x-mail::panel>
**Return not approved.**

Unfortunately your return request was not approved. If you believe this is in error, please reply to this email.
</x-mail::panel>
@elseif ($returnRequest->status === 'refunded')
<x-mail::panel>
**Refund issued!**

A refund of **€{{ number_format($returnRequest->refund_amount, 2) }}** has been issued. Please allow 5–10 business days for it to appear on your statement.
</x-mail::panel>
@endif

@if ($returnRequest->notes)
**Note from our team:**
{{ $returnRequest->notes }}
@endif

| | |
|:--|:--|
| **Order** | {{ $order->order_number }} |
| **Decision Date** | {{ $returnRequest->resolved_at?->format('d M Y') ?? now()->format('d M Y') }} |
@if ($returnRequest->refund_amount)
| **Refund Amount** | €{{ number_format($returnRequest->refund_amount, 2) }} |
@endif

<x-mail::button :url="url('/account/orders/' . $order->id)">
View Order
</x-mail::button>

Thanks,<br>
{{ $siteName }}
</x-mail::message>
