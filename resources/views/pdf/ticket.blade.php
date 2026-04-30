<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #1f2937;
            background: #ffffff;
        }

        .ticket {
            width: 100%;
            height: 280px;
            display: flex;
            border: 2px solid #4f46e5;
            border-radius: 8px;
            overflow: hidden;
        }

        .ticket-left {
            background: #4f46e5;
            width: 200px;
            padding: 24px 20px;
            color: #ffffff;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            flex-shrink: 0;
        }

        .site-name {
            font-size: 14px;
            font-weight: bold;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .ticket-type {
            font-size: 10px;
            opacity: 0.8;
            margin-top: 4px;
        }

        .event-name {
            font-size: 16px;
            font-weight: bold;
            line-height: 1.3;
            margin-top: 16px;
        }

        .event-date {
            font-size: 11px;
            opacity: 0.85;
            margin-top: 6px;
        }

        .order-ref {
            font-size: 10px;
            opacity: 0.7;
            margin-top: 4px;
        }

        .ticket-divider {
            width: 2px;
            background: repeating-linear-gradient(
                to bottom,
                #4f46e5 0px,
                #4f46e5 8px,
                #ffffff 8px,
                #ffffff 16px
            );
            flex-shrink: 0;
        }

        .ticket-right {
            flex: 1;
            padding: 24px 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .attendee-section .label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #6b7280;
        }

        .attendee-section .value {
            font-size: 14px;
            font-weight: 600;
            color: #111827;
            margin-top: 2px;
        }

        .ticket-code-section {
            text-align: center;
        }

        .ticket-code-label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #6b7280;
            margin-bottom: 4px;
        }

        .ticket-code {
            font-size: 18px;
            font-weight: bold;
            color: #4f46e5;
            letter-spacing: 0.1em;
        }

        .ticket-footer {
            font-size: 9px;
            color: #9ca3af;
            text-align: center;
        }

        .qr-section {
            width: 180px;
            padding: 24px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border-left: 1px dashed #e5e7eb;
            flex-shrink: 0;
        }

        .qr-section img {
            width: 120px;
            height: 120px;
        }

        .qr-label {
            font-size: 9px;
            color: #9ca3af;
            margin-top: 8px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="ticket">
        <!-- Left panel -->
        <div class="ticket-left">
            <div>
                <div class="site-name">{{ $siteName }}</div>
                <div class="ticket-type">Booking Ticket</div>
            </div>
            <div>
                <div class="event-name">{{ $ticket->orderItem->product_name }}</div>
                @if($ticket->orderItem->options['event_date'] ?? null)
                    <div class="event-date">{{ \Carbon\Carbon::parse($ticket->orderItem->options['event_date'])->format('d M Y') }}</div>
                @endif
                <div class="order-ref">Order: {{ $order->order_number }}</div>
            </div>
            <div style="font-size: 9px; opacity: 0.6;">
                Qty: {{ $ticket->orderItem->quantity }}
            </div>
        </div>

        <!-- Dashed divider -->
        <div class="ticket-divider"></div>

        <!-- Middle panel -->
        <div class="ticket-right">
            <div class="attendee-section">
                <div class="label">Ticket Holder</div>
                <div class="value">{{ $ticket->attendee_name ?? 'Guest' }}</div>
            </div>

            <div class="ticket-code-section">
                <div class="ticket-code-label">Ticket Code</div>
                <div class="ticket-code">{{ $ticket->ticket_code }}</div>
            </div>

            <div class="ticket-footer">
                Present this ticket at the venue &bull; Non-transferable
            </div>
        </div>

        <!-- QR panel -->
        <div class="qr-section">
            {!! $ticket->qr_code !!}
            <div class="qr-label">Scan to verify</div>
        </div>
    </div>
</body>
</html>
