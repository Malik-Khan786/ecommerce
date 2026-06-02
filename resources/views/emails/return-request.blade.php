<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Return Request Submitted</title>
    <style>
        body { margin:0; padding:0; font-family:Arial,sans-serif; background:#f4f4f4; color:#333; }
        .wrapper { max-width:600px; margin:40px auto; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.08); }
        .header { background:#f97316; padding:28px 32px; text-align:center; }
        .header h1 { margin:0; color:#fff; font-size:22px; }
        .header p  { margin:4px 0 0; color:rgba(255,255,255,0.85); font-size:14px; }
        .body { padding:32px; }
        .alert-icon { text-align:center; margin:0 0 20px; font-size:52px; }
        .info-box { background:#fff7ed; border:1px solid #fed7aa; border-radius:12px; padding:16px 20px; margin:20px 0; }
        .info-box p { margin:6px 0; font-size:14px; }
        .info-box .label { color:#9a3412; font-weight:bold; min-width:130px; display:inline-block; }
        .description-box { background:#f9fafb; border-left:4px solid #f97316; border-radius:0 8px 8px 0; padding:14px 16px; font-size:14px; color:#4b5563; margin:20px 0; line-height:1.6; }
        .badge { display:inline-block; background:#f97316; color:#fff; font-size:12px; font-weight:bold; padding:3px 10px; border-radius:20px; text-transform:uppercase; letter-spacing:0.5px; }
        .footer { text-align:center; padding:20px 32px; font-size:12px; color:#9ca3af; border-top:1px solid #f3f4f6; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <h1>Huzaifa Mobile</h1>
        <p>Return Request Notification</p>
    </div>
    <div class="body">
        <div class="alert-icon">&#x1F4E6;</div>
        <p style="font-size:18px;font-weight:bold;text-align:center;margin:0 0 6px;">New Return Request Received</p>
        <p style="text-align:center;color:#6b7280;font-size:14px;margin:0 0 24px;">A customer has submitted a return request. Please review it in the admin panel.</p>

        <div class="info-box">
            <p><span class="label">Order Number:</span> {{ $returnRequest->order->order_number }}</p>
            <p><span class="label">Customer Name:</span> {{ $returnRequest->user->name ?? $returnRequest->order->shipping_name }}</p>
            <p><span class="label">Reason:</span>
                <span class="badge">{{ str_replace('_', ' ', $returnRequest->reason) }}</span>
            </p>
            <p><span class="label">Submitted At:</span> {{ $returnRequest->created_at->format('d M Y, h:i A') }}</p>
        </div>

        @if($returnRequest->description)
        <p style="font-size:14px;font-weight:bold;margin:0 0 8px;color:#374151;">Customer's Description:</p>
        <div class="description-box">
            {{ $returnRequest->description }}
        </div>
        @endif

        <p style="font-size:13px;color:#6b7280;text-align:center;margin:24px 0 0;">
            Log in to the admin panel to approve, reject, or follow up on this request.
        </p>
    </div>
    <div class="footer">
        This is an automated notification from Huzaifa Mobile.<br>
        &copy; {{ date('Y') }} Huzaifa Mobile &mdash; <a href="mailto:support@huzaifamobile.pk" style="color:#f97316;text-decoration:none;">support@huzaifamobile.pk</a>
    </div>
</div>
</body>
</html>
