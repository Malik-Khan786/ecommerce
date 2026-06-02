<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>This Week at Huzaifa Mobile</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: #f5f5f5; font-family: 'Helvetica Neue', Arial, sans-serif; color: #333; }
        .wrapper { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #f97316, #ea580c); border-radius: 16px 16px 0 0; padding: 36px 32px; text-align: center; }
        .header h1 { color: #fff; font-size: 26px; font-weight: 800; letter-spacing: -0.5px; }
        .header p { color: rgba(255,255,255,0.85); font-size: 14px; margin-top: 8px; }
        .fire-badge { background: rgba(255,255,255,0.15); display: inline-block; padding: 4px 14px; border-radius: 20px; color: #fff; font-size: 13px; margin-bottom: 12px; }
        .body { background: #fff; padding: 32px; }
        .intro { font-size: 15px; color: #555; margin-bottom: 28px; line-height: 1.6; }
        .section-title { font-size: 18px; font-weight: 700; color: #1a1a1a; margin-bottom: 20px; border-left: 4px solid #f97316; padding-left: 12px; }
        .products-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .product-card { border: 1px solid #efefef; border-radius: 12px; overflow: hidden; transition: box-shadow 0.2s; }
        .product-card img { width: 100%; height: 160px; object-fit: cover; background: #f9f9f9; display: block; }
        .product-card .info { padding: 14px; }
        .product-card .name { font-size: 13px; font-weight: 600; color: #1a1a1a; line-height: 1.4; margin-bottom: 6px; }
        .product-card .price { font-size: 15px; font-weight: 800; color: #f97316; margin-bottom: 12px; }
        .product-card .original-price { font-size: 12px; color: #999; text-decoration: line-through; margin-left: 6px; font-weight: 400; }
        .btn-shop { display: block; text-align: center; background: #f97316; color: #fff; text-decoration: none; padding: 9px 0; border-radius: 8px; font-size: 13px; font-weight: 600; }
        .btn-shop:hover { background: #ea580c; }
        .cta-section { margin-top: 28px; text-align: center; padding: 24px; background: #fff7ed; border-radius: 12px; border: 1px solid #fed7aa; }
        .cta-section p { font-size: 14px; color: #7c3100; margin-bottom: 14px; }
        .btn-main { display: inline-block; background: #f97316; color: #fff; text-decoration: none; padding: 12px 32px; border-radius: 10px; font-weight: 700; font-size: 15px; }
        .footer { background: #1a1a1a; border-radius: 0 0 16px 16px; padding: 24px 32px; text-align: center; }
        .footer p { color: #888; font-size: 12px; line-height: 1.7; }
        .footer a { color: #f97316; text-decoration: none; }
        @media (max-width: 480px) {
            .products-grid { grid-template-columns: 1fr; }
            .header { padding: 24px 20px; }
            .body { padding: 20px; }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <div class="fire-badge">🔥 Weekly Picks</div>
            <h1>This Week at Huzaifa Mobile</h1>
            <p>Handpicked deals just for you — don't miss out!</p>
        </div>

        <div class="body">
            <p class="intro">
                Hi there! 👋 We've curated the hottest products this week at <strong>Huzaifa Mobile</strong>.
                Check out our featured picks and grab yours before they sell out!
            </p>

            <h2 class="section-title">Featured This Week</h2>

            @if($products->count())
            <div class="products-grid">
                @foreach($products as $product)
                <div class="product-card">
                    @if($product->thumbnail)
                        <img src="{{ $product->thumbnail }}" alt="{{ $product->name }}" />
                    @else
                        <div style="width:100%;height:160px;background:#f3f4f6;display:flex;align-items:center;justify-content:center;color:#ccc;font-size:32px;">📱</div>
                    @endif
                    <div class="info">
                        <p class="name">{{ $product->name }}</p>
                        <p class="price">
                            Rs. {{ number_format($product->sale_price ?? $product->price, 0) }}
                            @if($product->sale_price && $product->sale_price < $product->price)
                                <span class="original-price">Rs. {{ number_format($product->price, 0) }}</span>
                            @endif
                        </p>
                        <a href="{{ config('app.url') }}/products/{{ $product->slug }}" class="btn-shop">Shop Now →</a>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <p style="color:#aaa;text-align:center;padding:20px 0;">Check back soon for new arrivals!</p>
            @endif

            <div class="cta-section">
                <p>Discover hundreds more deals on our store. New arrivals added every week!</p>
                <a href="{{ config('app.url') }}/products" class="btn-main">Browse All Products</a>
            </div>
        </div>

        <div class="footer">
            <p>
                You're receiving this because you subscribed at Huzaifa Mobile.<br />
                <a href="{{ config('app.url') }}">Visit our store</a> &nbsp;·&nbsp;
                <a href="mailto:{{ config('mail.from.address') }}?subject=Unsubscribe">Unsubscribe</a>
            </p>
            <p style="margin-top:8px;color:#555;">© {{ date('Y') }} Huzaifa Mobile. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
