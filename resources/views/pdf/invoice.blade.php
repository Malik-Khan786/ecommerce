<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size:13px; color:#333; }
    .header { background:#f97316; color:#fff; padding:24px 32px; }
    .header h1 { font-size:24px; font-weight:bold; }
    .header p { font-size:12px; opacity:0.85; margin-top:2px; }
    .badge { background:#fff; color:#f97316; padding:4px 12px; border-radius:4px; font-size:11px; font-weight:bold; float:right; margin-top:-4px; }
    .body { padding:24px 32px; }
    .meta { display:flex; justify-content:space-between; margin-bottom:24px; }
    .meta-box { width:48%; }
    .meta-box h3 { font-size:11px; text-transform:uppercase; color:#999; margin-bottom:6px; letter-spacing:0.5px; }
    .meta-box p { font-size:13px; line-height:1.6; }
    table { width:100%; border-collapse:collapse; margin-bottom:16px; }
    thead tr { background:#f97316; color:#fff; }
    thead th { padding:10px 12px; text-align:left; font-size:12px; font-weight:bold; }
    tbody tr { border-bottom:1px solid #f0f0f0; }
    tbody tr:nth-child(even) { background:#fafafa; }
    tbody td { padding:10px 12px; font-size:13px; }
    .totals { float:right; width:260px; }
    .totals table { margin:0; }
    .totals td { padding:6px 8px; font-size:13px; }
    .totals tr:last-child td { font-weight:bold; font-size:15px; color:#f97316; border-top:2px solid #f97316; padding-top:10px; }
    .footer { text-align:center; color:#999; font-size:11px; padding:16px 32px; border-top:1px solid #eee; margin-top:40px; }
    .clearfix::after { content:''; display:table; clear:both; }
</style>
</head>
<body>
<div class="header">
    <span class="badge">INVOICE</span>
    <h1>Huzaifa Mobile</h1>
    <p>Pakistan's Best Mobile Store | support@huzaifamobile.pk | 0300-1234567</p>
</div>

<div class="body">
    <div class="meta">
        <div class="meta-box">
            <h3>Invoice Details</h3>
            <p><strong>Order:</strong> {{ $order->order_number }}</p>
            <p><strong>Date:</strong> {{ $order->created_at->format('d M Y') }}</p>
            <p><strong>Payment:</strong> {{ strtoupper($order->payment_method) }}</p>
            @if($order->estimated_delivery_date)
            <p><strong>Est. Delivery:</strong> {{ \Carbon\Carbon::parse($order->estimated_delivery_date)->format('d M Y') }}</p>
            @endif
        </div>
        <div class="meta-box">
            <h3>Bill To</h3>
            <p><strong>{{ $order->shipping_name }}</strong></p>
            <p>{{ $order->shipping_phone }}</p>
            <p>{{ $order->shipping_address }}</p>
            <p>{{ $order->shipping_city }}@if($order->shipping_state), {{ $order->shipping_state }}@endif</p>
            <p>{{ $order->shipping_country }}</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Product</th>
                <th style="text-align:center">Qty</th>
                <th style="text-align:right">Unit Price</th>
                <th style="text-align:right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $item->product_name }}</td>
                <td style="text-align:center">{{ $item->quantity }}</td>
                <td style="text-align:right">Rs. {{ number_format($item->price, 0) }}</td>
                <td style="text-align:right">Rs. {{ number_format($item->subtotal, 0) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="clearfix">
        <div class="totals">
            <table>
                <tr><td>Subtotal</td><td style="text-align:right">Rs. {{ number_format($order->subtotal, 0) }}</td></tr>
                @if($order->discount > 0)
                <tr><td style="color:green">Discount</td><td style="text-align:right;color:green">- Rs. {{ number_format($order->discount, 0) }}</td></tr>
                @endif
                <tr><td>Shipping</td><td style="text-align:right">{{ $order->shipping_cost > 0 ? 'Rs. '.number_format($order->shipping_cost,0) : 'FREE' }}</td></tr>
                <tr><td>Total</td><td style="text-align:right">Rs. {{ number_format($order->total, 0) }}</td></tr>
            </table>
        </div>
    </div>
</div>

<div class="footer">
    Thank you for shopping with Huzaifa Mobile! For any queries, contact us at support@huzaifamobile.pk
</div>
</body>
</html>
