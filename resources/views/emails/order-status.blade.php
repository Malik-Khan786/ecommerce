<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Order Status Update</title>
    <style>
        body { margin:0; padding:0; font-family: Arial, sans-serif; background:#f4f4f4; color:#333; }
        .wrapper { max-width:600px; margin:40px auto; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.08); }
        .header { background:#f97316; padding:28px 32px; text-align:center; }
        .header h1 { margin:0; color:#fff; font-size:22px; }
        .header p  { margin:4px 0 0; color:#fff; opacity:0.85; font-size:14px; }
        .body { padding:32px; }
        .status-badge { display:inline-block; padding:8px 20px; border-radius:99px; font-weight:bold; font-size:15px; margin:16px 0; }
        .status-pending    { background:#fef3c7; color:#92400e; }
        .status-confirmed  { background:#dbeafe; color:#1e40af; }
        .status-processing { background:#e0e7ff; color:#3730a3; }
        .status-shipped    { background:#ede9fe; color:#5b21b6; }
        .status-delivered  { background:#d1fae5; color:#065f46; }
        .status-cancelled  { background:#fee2e2; color:#991b1b; }
        .order-info { background:#f9fafb; border-radius:8px; padding:16px; margin:20px 0; }
        .order-info table { width:100%; border-collapse:collapse; font-size:14px; }
        .order-info td { padding:6px 0; }
        .order-info td:first-child { color:#6b7280; width:140px; }
        .order-info td:last-child { font-weight:600; }
        .items-table { width:100%; border-collapse:collapse; font-size:14px; margin:16px 0; }
        .items-table th { text-align:left; border-bottom:2px solid #e5e7eb; padding:8px 0; color:#6b7280; font-size:12px; text-transform:uppercase; }
        .items-table td { padding:10px 0; border-bottom:1px solid #f3f4f6; vertical-align:top; }
        .total-row { font-weight:bold; font-size:15px; }
        .total-row td { border-bottom:none; padding-top:14px; }
        .message-box { background:#fff7ed; border-left:4px solid #f97316; padding:14px 16px; border-radius:0 8px 8px 0; margin:20px 0; font-size:14px; color:#92400e; }
        .footer { text-align:center; padding:20px 32px; font-size:12px; color:#9ca3af; border-top:1px solid #f3f4f6; }
        .btn { display:inline-block; background:#f97316; color:#fff; text-decoration:none; padding:12px 28px; border-radius:8px; font-weight:bold; font-size:14px; margin:16px 0; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <h1>Huzaifa Mobile</h1>
        <p>Order Status Update</p>
    </div>

    <div class="body">
        <p style="font-size:16px;">Hi <strong>{{ $order->shipping_name }}</strong>,</p>
        <p style="color:#6b7280; font-size:14px;">
            Your order status has been updated. Here are the details:
        </p>

        <!-- Status badge -->
        <div style="text-align:center; margin:24px 0;">
            <p style="color:#6b7280; font-size:13px; margin:0 0 8px;">Current Status</p>
            <span class="status-badge status-{{ $order->status }}">
                @switch($order->status)
                    @case('pending')    ⏳ Pending      @break
                    @case('confirmed')  ✅ Confirmed     @break
                    @case('processing') 🔧 Processing    @break
                    @case('shipped')    🚚 Shipped       @break
                    @case('delivered')  🎉 Delivered     @break
                    @case('cancelled')  ❌ Cancelled     @break
                    @default {{ ucfirst($order->status) }}
                @endswitch
            </span>
        </div>

        <!-- Status message -->
        <div class="message-box">
            @switch($order->status)
                @case('confirmed')
                    Your order has been confirmed! We are preparing it for dispatch.
                    @break
                @case('processing')
                    Your order is being processed and packed with care.
                    @break
                @case('shipped')
                    Great news! Your order is on its way. Our delivery partner will contact you soon.
                    @break
                @case('delivered')
                    Your order has been delivered. We hope you love your purchase! 🎁
                    @break
                @case('cancelled')
                    Your order has been cancelled. If you have any questions, please contact us.
                    @break
                @default
                    Your order status has been updated.
            @endswitch
        </div>

        <!-- Order info -->
        <div class="order-info">
            <table>
                <tr><td>Order Number</td><td>{{ $order->order_number }}</td></tr>
                <tr><td>Payment Method</td><td>{{ strtoupper($order->payment_method) }}</td></tr>
                <tr><td>Delivery Address</td><td>{{ $order->shipping_address }}, {{ $order->shipping_city }}</td></tr>
                <tr><td>Contact</td><td>{{ $order->shipping_phone }}</td></tr>
            </table>
        </div>

        <!-- Items -->
        <table class="items-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th style="text-align:right;">Qty</th>
                    <th style="text-align:right;">Price</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td style="text-align:right; color:#6b7280;">× {{ $item->quantity }}</td>
                    <td style="text-align:right;">Rs. {{ number_format($item->subtotal, 0) }}</td>
                </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="2">Total</td>
                    <td style="text-align:right; color:#f97316;">Rs. {{ number_format($order->total, 0) }}</td>
                </tr>
            </tbody>
        </table>

        @if($order->status === 'delivered' && $order->ack_token)
        <!-- Acknowledgement section -->
        <div style="background:#f0fdf4; border:1px solid #86efac; border-radius:12px; padding:20px; margin:24px 0; text-align:center;">
            <p style="font-size:16px; font-weight:bold; color:#15803d; margin:0 0 6px;">Did you receive your order?</p>
            <p style="font-size:13px; color:#166534; margin:0 0 16px;">Please confirm receipt so we know your order was delivered safely.</p>
            <div style="display:flex; gap:12px; justify-content:center; flex-wrap:wrap;">
                <a href="{{ config('app.url') }}/order-received/{{ $order->ack_token }}?status=received"
                   style="background:#16a34a; color:#fff; text-decoration:none; padding:12px 24px; border-radius:8px; font-weight:bold; font-size:14px; display:inline-block;">
                    ✅ Yes, I Received It
                </a>
                <a href="{{ config('app.url') }}/order-received/{{ $order->ack_token }}?status=issue"
                   style="background:#fff; color:#dc2626; text-decoration:none; padding:12px 24px; border-radius:8px; font-weight:bold; font-size:14px; border:2px solid #fca5a5; display:inline-block;">
                    ⚠️ I Have an Issue
                </a>
            </div>
            <p style="font-size:11px; color:#6b7280; margin:12px 0 0;">This link is unique to your order and expires after use.</p>
        </div>
        @endif

        <p style="font-size:13px; color:#6b7280; margin-top:24px;">
            For any questions, reply to this email or contact us:<br>
            📞 0300-1234567 &nbsp;|&nbsp; ✉️ support@huzaifamobile.pk
        </p>
    </div>

    <div class="footer">
        © {{ date('Y') }} Huzaifa Mobile. All rights reserved.<br>
        This email was sent because you placed an order with us.
    </div>
</div>
</body>
</html>
