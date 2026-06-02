<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Order Confirmed</title>
    <style>
        body { margin:0; padding:0; font-family:Arial,sans-serif; background:#f4f4f4; color:#333; }
        .wrapper { max-width:600px; margin:40px auto; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.08); }
        .header { background:#f97316; padding:28px 32px; text-align:center; }
        .header h1 { margin:0; color:#fff; font-size:22px; }
        .header p  { margin:4px 0 0; color:rgba(255,255,255,0.85); font-size:14px; }
        .body { padding:32px; }
        .success-icon { text-align:center; margin:0 0 20px; font-size:52px; }
        .order-box { background:#fff7ed; border:1px solid #fed7aa; border-radius:12px; padding:16px 20px; margin:20px 0; }
        .order-box p { margin:4px 0; font-size:14px; }
        .items-table { width:100%; border-collapse:collapse; font-size:14px; margin:16px 0; }
        .items-table th { text-align:left; border-bottom:2px solid #e5e7eb; padding:8px 0; color:#6b7280; font-size:12px; text-transform:uppercase; }
        .items-table td { padding:10px 0; border-bottom:1px solid #f3f4f6; }
        .totals { margin:16px 0; font-size:14px; }
        .totals tr td { padding:5px 0; }
        .totals tr:last-child td { font-weight:bold; font-size:15px; border-top:2px solid #e5e7eb; padding-top:10px; }
        .totals td:last-child { text-align:right; }
        .footer { text-align:center; padding:20px 32px; font-size:12px; color:#9ca3af; border-top:1px solid #f3f4f6; }
        .contact { background:#f9fafb; border-radius:8px; padding:14px 16px; font-size:13px; color:#6b7280; margin:20px 0; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <h1>Huzaifa Mobile</h1>
        <p>Order Confirmation</p>
    </div>
    <div class="body">
        <div class="success-icon">🎉</div>
        <p style="font-size:18px;font-weight:bold;text-align:center;margin:0 0 6px;">Thank you, {{ $order->shipping_name }}!</p>
        <p style="text-align:center;color:#6b7280;font-size:14px;margin:0 0 24px;">Your order has been placed successfully.</p>

        <div class="order-box">
            <p><strong>Order Number:</strong> {{ $order->order_number }}</p>
            <p><strong>Payment:</strong> {{ strtoupper($order->payment_method) }}</p>
            <p><strong>Deliver to:</strong> {{ $order->shipping_address }}, {{ $order->shipping_city }}</p>
            <p><strong>Contact:</strong> {{ $order->shipping_phone }}</p>
        </div>

        <table class="items-table">
            <thead>
                <tr><th>Item</th><th style="text-align:right">Qty</th><th style="text-align:right">Price</th></tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td style="text-align:right;color:#6b7280">×{{ $item->quantity }}</td>
                    <td style="text-align:right">Rs. {{ number_format($item->subtotal, 0) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <table class="totals" width="100%">
            <tr><td style="color:#6b7280">Subtotal</td><td>Rs. {{ number_format($order->subtotal, 0) }}</td></tr>
            @if($order->discount > 0)
            <tr><td style="color:#16a34a">Discount</td><td style="color:#16a34a">− Rs. {{ number_format($order->discount, 0) }}</td></tr>
            @endif
            <tr><td style="color:#6b7280">Shipping</td><td>{{ $order->shipping_cost > 0 ? 'Rs. '.number_format($order->shipping_cost,0) : 'FREE' }}</td></tr>
            <tr><td>Total</td><td style="color:#f97316">Rs. {{ number_format($order->total, 0) }}</td></tr>
        </table>

        <div class="contact">
            We'll update you when your order ships. Questions? Contact us:<br>
            📞 0300-1234567 &nbsp;|&nbsp; ✉️ support@huzaifamobile.pk
        </div>
    </div>
    <div class="footer">© {{ date('Y') }} Huzaifa Mobile. All rights reserved.</div>
</div>
</body>
</html>
