<x-mail::message>
# 🎁 You've Got a Gift Card!

Hi {{ $recipientName ?? 'there' }},

Someone special sent you a gift card to {{ $siteName }}. Happy shopping!

@if ($personalMessage)
<x-mail::panel>
*"{{ $personalMessage }}"*
</x-mail::panel>
@endif

<table style="width:100%; margin: 24px 0; background-color: #f5f5ff; border-radius: 12px; border: 2px dashed #4f46e5;">
<tr>
  <td style="padding: 28px; text-align: center;">
    <p style="margin:0; font-size:12px; color:#6b7280; text-transform:uppercase; letter-spacing:0.1em; font-weight:600;">Your Gift Card Code</p>
    <p style="margin:12px 0; font-size:28px; font-weight:bold; color:#4f46e5; letter-spacing:0.15em; font-family:monospace;">{{ $giftCard->code }}</p>
    <p style="margin:0; font-size:20px; font-weight:bold; color:#1e1b4b;">{{ number_format($giftCard->remaining_balance, 2) }} {{ $currency }}</p>
    @if ($giftCard->expires_at)
    <p style="margin:8px 0 0; font-size:13px; color:#6b7280;">Valid until {{ $giftCard->expires_at->format('d M Y') }}</p>
    @else
    <p style="margin:8px 0 0; font-size:13px; color:#6b7280;">No expiry date — use whenever you're ready</p>
    @endif
  </td>
</tr>
</table>

**How to use it:**

1. Add items to your cart
2. Enter code **{{ $giftCard->code }}** at checkout
3. Your balance of **{{ number_format($giftCard->remaining_balance, 2) }} {{ $currency }}** will be applied automatically

<x-mail::button :url="url('/shop')">
Start Shopping
</x-mail::button>

If you have any questions about your gift card, reply to this email and we'll help you out.

Thanks,<br>
{{ $siteName }}
</x-mail::message>
