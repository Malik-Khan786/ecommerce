<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Inventory Alert</title>
    <style>
        body { margin:0; padding:0; font-family:Arial,sans-serif; background:#f4f4f4; color:#333; }
        .wrapper { max-width:640px; margin:40px auto; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.08); }
        .header { background:#f97316; padding:28px 32px; text-align:center; }
        .header h1 { margin:0; color:#fff; font-size:22px; }
        .header p { margin:6px 0 0; color:rgba(255,255,255,0.9); font-size:14px; }
        .body { padding:32px; }
        .section { margin:0 0 28px; }
        .section-title { font-size:16px; font-weight:bold; margin:0 0 12px; padding:10px 16px; border-radius:8px; }
        .section-title.low { background:#fff7ed; color:#c2410c; border-left:4px solid #f97316; }
        .section-title.out { background:#fef2f2; color:#b91c1c; border-left:4px solid #ef4444; }
        table { width:100%; border-collapse:collapse; font-size:14px; }
        th { text-align:left; background:#f9fafb; border-bottom:2px solid #e5e7eb; padding:10px 12px; color:#6b7280; font-size:12px; text-transform:uppercase; letter-spacing:0.04em; }
        td { padding:10px 12px; border-bottom:1px solid #f3f4f6; vertical-align:middle; }
        tr:last-child td { border-bottom:none; }
        .stock-badge { display:inline-block; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:bold; }
        .stock-low { background:#fff7ed; color:#c2410c; }
        .stock-out { background:#fef2f2; color:#b91c1c; }
        .empty { color:#9ca3af; font-size:13px; padding:16px; text-align:center; }
        .summary-box { background:#f9fafb; border-radius:10px; padding:16px 20px; margin:0 0 24px; display:flex; gap:24px; justify-content:center; }
        .summary-item { text-align:center; }
        .summary-item .num { font-size:28px; font-weight:bold; }
        .summary-item .lbl { font-size:12px; color:#6b7280; margin-top:2px; }
        .num-orange { color:#f97316; }
        .num-red { color:#ef4444; }
        .footer { text-align:center; padding:20px 32px; font-size:12px; color:#9ca3af; border-top:1px solid #f3f4f6; }
        .admin-link { display:inline-block; margin-top:20px; background:#f97316; color:#fff; text-decoration:none; padding:12px 28px; border-radius:8px; font-size:14px; font-weight:bold; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <h1>Inventory Alert Report</h1>
        <p>{{ now()->format('l, F j, Y — g:i A') }}</p>
    </div>
    <div class="body">
        <p style="color:#6b7280;font-size:14px;margin:0 0 20px;">Here is a summary of products that need your attention.</p>

        <div class="summary-box">
            <div class="summary-item">
                <div class="num num-orange">{{ count($lowStock) }}</div>
                <div class="lbl">Low Stock</div>
            </div>
            <div class="summary-item">
                <div class="num num-red">{{ count($outOfStock) }}</div>
                <div class="lbl">Out of Stock</div>
            </div>
        </div>

        <!-- Low Stock -->
        <div class="section">
            <div class="section-title low">⚠️ Low Stock (1–10 units remaining)</div>
            @if(count($lowStock))
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>SKU</th>
                            <th style="text-align:right">Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lowStock as $product)
                        <tr>
                            <td style="font-weight:500">{{ $product->name }}</td>
                            <td style="color:#9ca3af;font-size:12px">{{ $product->sku ?? '—' }}</td>
                            <td style="text-align:right">
                                <span class="stock-badge stock-low">{{ $product->stock }} left</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="empty">No low stock products.</p>
            @endif
        </div>

        <!-- Out of Stock -->
        <div class="section">
            <div class="section-title out">🚫 Out of Stock</div>
            @if(count($outOfStock))
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>SKU</th>
                            <th style="text-align:right">Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($outOfStock as $product)
                        <tr>
                            <td style="font-weight:500">{{ $product->name }}</td>
                            <td style="color:#9ca3af;font-size:12px">{{ $product->sku ?? '—' }}</td>
                            <td style="text-align:right">
                                <span class="stock-badge stock-out">Out of stock</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="empty">No out-of-stock products.</p>
            @endif
        </div>

        <div style="text-align:center">
            <a href="{{ config('app.url') }}/admin/products" class="admin-link">Manage Products</a>
        </div>
    </div>
    <div class="footer">© {{ date('Y') }} Huzaifa Mobile Admin. This is an automated alert.</div>
</div>
</body>
</html>
