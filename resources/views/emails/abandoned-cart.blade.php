<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Your cart is waiting!</title>
    <style>
        body { margin:0; padding:0; font-family:Arial,sans-serif; background:#f4f4f4; color:#333; }
        .wrapper { max-width:600px; margin:40px auto; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.08); }
        .header { background:#f97316; padding:32px; text-align:center; }
        .header h1 { margin:0; color:#fff; font-size:24px; font-weight:bold; }
        .header p { margin:8px 0 0; color:rgba(255,255,255,0.9); font-size:15px; }
        .body { padding:32px; }
        .greeting { font-size:16px; margin:0 0 20px; color:#374151; }
        .items-section { margin:20px 0; }
        .items-section h2 { font-size:15px; color:#6b7280; text-transform:uppercase; letter-spacing:0.05em; margin:0 0 12px; }
        .item-row { display:flex; align-items:center; gap:16px; padding:12px 0; border-bottom:1px solid #f3f4f6; }
        .item-row:last-child { border-bottom:none; }
        .item-img { width:64px; height:64px; object-fit:cover; border-radius:8px; background:#f9fafb; flex-shrink:0; }
        .item-img-placeholder { width:64px; height:64px; border-radius:8px; background:#fed7aa; display:flex; align-items:center; justify-content:center; font-size:24px; flex-shrink:0; }
        .item-details { flex:1; }
        .item-name { font-weight:bold; font-size:14px; color:#111827; margin:0 0 4px; }
        .item-qty { font-size:13px; color:#6b7280; margin:0; }
        .item-price { font-size:15px; font-weight:bold; color:#f97316; white-space:nowrap; }
        .total-box { background:#fff7ed; border:2px solid #fed7aa; border-radius:12px; padding:16px 20px; margin:20px 0; display:flex; justify-content:space-between; align-items:center; }
        .total-box span { font-size:15px; color:#6b7280; }
        .total-box strong { font-size:20px; color:#f97316; font-weight:bold; }
        .cta-wrapper { text-align:center; margin:28px 0; }
        .cta-btn { display:inline-block; background:#f97316; color:#fff; text-decoration:none; padding:16px 36px; border-radius:10px; font-size:16px; font-weight:bold; letter-spacing:0.02em; }
        .cta-btn:hover { background:#ea6c0a; }
        .note { font-size:13px; color:#9ca3af; text-align:center; margin:0 0 20px; }
        .footer { text-align:center; padding:20px 32px; font-size:12px; color:#9ca3af; border-top:1px solid #f3f4f6; }
        .contact { background:#f9fafb; border-radius:8px; padding:14px 16px; font-size:13px; color:#6b7280; margin:20px 0; text-align:center; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <h1>Your cart is waiting for you! 🛒</h1>
        <p>You left some great items behind — complete your purchase today!</p>
    </div>
    <div class="body">
        <p class="greeting">Hi {{ $user->name }},</p>
        <p style="font-size:14px;color:#6b7280;margin:0 0 20px;">
            We noticed you added items to your cart but didn't complete your purchase.
            Don't worry — we've saved them for you!
        </p>

        <div class="items-section">
            <h2>Items in your cart</h2>
            @php $total = 0; @endphp
            @foreach($cartItems as $item)
                @php
                    $product = $item->product;
                    $price   = $product->sale_price ?? $product->price;
                    $subtotal = $price * $item->quantity;
                    $total  += $subtotal;
                @endphp
                <div class="item-row">
                    @if($product->thumbnail)
                        <img src="{{ $product->thumbnail }}" alt="{{ $product->name }}" class="item-img" />
                    @else
                        <div class="item-img-placeholder">📦</div>
                    @endif
                    <div class="item-details">
                        <p class="item-name">{{ $product->name }}</p>
                        <p class="item-qty">Qty: {{ $item->quantity }}</p>
                    </div>
                    <span class="item-price">Rs. {{ number_format($subtotal, 0) }}</span>
                </div>
            @endforeach
        </div>

        <div class="total-box">
            <span>Estimated Total</span>
            <strong>Rs. {{ number_format($total, 0) }}</strong>
        </div>

        <div class="cta-wrapper">
            <a href="{{ config('app.url') }}/cart" class="cta-btn">Complete Your Purchase</a>
        </div>

        <p class="note">Items in your cart are not reserved and may sell out.</p>

        <div class="contact">
            Need help? Contact us: 📞 0300-1234567 &nbsp;|&nbsp; ✉️ support@huzaifamobile.pk
        </div>
    </div>
    <div class="footer">© {{ date('Y') }} Huzaifa Mobile. All rights reserved.<br>You received this because you have an account with us.</div>
</div>
</body>
</html>
