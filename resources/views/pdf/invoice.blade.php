<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Invoice {{ $order->order_number }}</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; background: #fff; }
  .header { background: #4f46e5; color: #fff; padding: 28px 36px; }
  .header h1 { font-size: 22px; font-weight: bold; letter-spacing: 0.02em; }
  .header p { font-size: 11px; margin-top: 4px; opacity: 0.8; }
  .body { padding: 32px 36px; }
  .meta { display: flex; justify-content: space-between; margin-bottom: 28px; }
  .meta-block h3 { font-size: 10px; text-transform: uppercase; letter-spacing: 0.06em; color: #6b7280; margin-bottom: 6px; }
  .meta-block p { font-size: 12px; color: #111827; line-height: 1.6; }
  .section-title { font-size: 11px; text-transform: uppercase; letter-spacing: 0.06em; color: #6b7280; border-bottom: 1px solid #e5e7eb; padding-bottom: 6px; margin-bottom: 12px; }
  table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
  table th { background: #f3f4f6; font-size: 10px; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280; padding: 8px 10px; text-align: left; }
  table td { padding: 9px 10px; border-bottom: 1px solid #f3f4f6; font-size: 12px; }
  table td.right { text-align: right; }
  .totals { float: right; width: 240px; }
  .totals table td { padding: 6px 10px; border-bottom: none; }
  .totals .total-row td { font-weight: bold; font-size: 13px; border-top: 2px solid #4f46e5; padding-top: 10px; color: #4f46e5; }
  .addresses { display: flex; gap: 40px; margin-top: 32px; }
  .address-block { flex: 1; }
  .address-block h3 { font-size: 10px; text-transform: uppercase; letter-spacing: 0.06em; color: #6b7280; margin-bottom: 6px; }
  .address-block p { font-size: 12px; color: #374151; line-height: 1.7; }
  .footer { margin-top: 48px; padding-top: 16px; border-top: 1px solid #e5e7eb; text-align: center; font-size: 10px; color: #9ca3af; }
  .badge { display: inline-block; padding: 3px 8px; border-radius: 9999px; font-size: 10px; font-weight: bold; }
  .badge-paid { background: #d1fae5; color: #065f46; }
  .badge-unpaid { background: #fef3c7; color: #92400e; }
</style>
</head>
<body>

<div class="header">
  <h1>{{ $siteName }}</h1>
  <p>Invoice</p>
</div>

<div class="body">

  <div class="meta">
    <div class="meta-block">
      <h3>Invoice Number</h3>
      <p>{{ $order->order_number }}</p>
    </div>
    <div class="meta-block">
      <h3>Date</h3>
      <p>{{ $order->created_at->format('d M Y') }}</p>
    </div>
    <div class="meta-block">
      <h3>Payment Status</h3>
      <p>
        <span class="badge {{ $order->payment_status === 'paid' ? 'badge-paid' : 'badge-unpaid' }}">
          {{ ucfirst($order->payment_status) }}
        </span>
      </p>
    </div>
    <div class="meta-block">
      <h3>Payment Method</h3>
      <p>{{ ucwords(str_replace('_', ' ', $order->payment_method ?? '—')) }}</p>
    </div>
  </div>

  <div class="section-title">Items</div>
  <table>
    <thead>
      <tr>
        <th>Product</th>
        <th>SKU</th>
        <th class="right">Unit Price</th>
        <th class="right">Qty</th>
        <th class="right">Subtotal</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($order->items as $item)
      <tr>
        <td>{{ $item->product_name }}</td>
        <td>{{ $item->product_sku ?? '—' }}</td>
        <td class="right">{{ number_format($item->unit_price, 2) }} {{ $currency }}</td>
        <td class="right">{{ $item->quantity }}</td>
        <td class="right">{{ number_format($item->subtotal, 2) }} {{ $currency }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>

  <div class="totals">
    <table>
      <tr>
        <td>Subtotal</td>
        <td class="right">{{ number_format($order->subtotal, 2) }} {{ $currency }}</td>
      </tr>
      @if ($order->discount_amount > 0)
      <tr>
        <td>Discount</td>
        <td class="right">−{{ number_format($order->discount_amount, 2) }} {{ $currency }}</td>
      </tr>
      @endif
      @if ($order->tax_amount > 0)
      <tr>
        <td>Tax</td>
        <td class="right">{{ number_format($order->tax_amount, 2) }} {{ $currency }}</td>
      </tr>
      @endif
      @if ($order->shipping_amount > 0)
      <tr>
        <td>Shipping</td>
        <td class="right">{{ number_format($order->shipping_amount, 2) }} {{ $currency }}</td>
      </tr>
      @endif
      <tr class="total-row">
        <td>Total</td>
        <td class="right">{{ number_format($order->total, 2) }} {{ $currency }}</td>
      </tr>
    </table>
  </div>

  <div style="clear:both;"></div>

  <div class="addresses">
    @if ($order->billing_address)
    <div class="address-block">
      <h3>Billing Address</h3>
      <p>
        {{ $order->billing_address['name'] ?? '' }}<br>
        {{ $order->billing_address['line1'] ?? '' }}<br>
        {{ $order->billing_address['city'] ?? '' }}@if(!empty($order->billing_address['zip'])), {{ $order->billing_address['zip'] }}@endif<br>
        {{ $order->billing_address['country'] ?? '' }}<br>
        {{ $order->billing_address['email'] ?? '' }}
      </p>
    </div>
    @endif
    @if ($order->shipping_address)
    <div class="address-block">
      <h3>Shipping Address</h3>
      <p>
        {{ $order->shipping_address['name'] ?? '' }}<br>
        {{ $order->shipping_address['line1'] ?? '' }}<br>
        {{ $order->shipping_address['city'] ?? '' }}@if(!empty($order->shipping_address['zip'])), {{ $order->shipping_address['zip'] }}@endif<br>
        {{ $order->shipping_address['country'] ?? '' }}
      </p>
    </div>
    @endif
  </div>

  <div class="footer">
    {{ $siteName }} &mdash; Thank you for your business.
  </div>

</div>
</body>
</html>
