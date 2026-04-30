<x-mail::message>
# Return Request Received

Hi {{ $returnRequest->user->name }},

We've received your return request for order **{{ $order->order_number }}**.

<x-mail::panel>
**What happens next?**

Our team will review your request within 1–2 business days and notify you once a decision has been made. If approved, you will receive instructions on how to send the item(s) back.
</x-mail::panel>

| | |
|:--|:--|
| **Order** | {{ $order->order_number }} |
| **Reason** | {{ $returnRequest->reason }} |
| **Request Date** | {{ $returnRequest->created_at->format('d M Y') }} |
| **Status** | Pending Review |

@if ($returnRequest->details)
**Your details:**
{{ $returnRequest->details }}
@endif

<x-mail::button :url="url('/account/orders/' . $order->id)">
View Order
</x-mail::button>

Thanks,<br>
{{ $siteName }}
</x-mail::message>
