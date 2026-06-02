<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EasyPaisaController extends Controller
{
    private string $storeId;
    private string $hashKey;
    private string $endpoint;

    public function __construct()
    {
        $this->storeId  = config('services.easypaisa.store_id');
        $this->hashKey  = config('services.easypaisa.hash_key');
        $this->endpoint = config('services.easypaisa.endpoint');
    }

    /**
     * Generate EasyPaisa redirect form data.
     * POST /api/payments/easypaisa
     */
    public function initiate(Request $request)
    {
        $request->validate([
            'order_uuid' => 'required|string',
        ]);

        if (!$this->storeId) {
            return response()->json(['message' => 'EasyPaisa is not configured yet.'], 503);
        }

        $order = Order::where('uuid', $request->order_uuid)
            ->where('user_id', $request->user()?->id)
            ->firstOrFail();

        $orderRefNumber = $order->order_number;
        $amount         = number_format($order->total, 2, '.', '');
        $txnDateTime    = now()->format('YmdHis');
        $expiryDateTime = now()->addHours(1)->format('YmdHis');
        $returnUrl      = url('/api/payments/easypaisa/callback');

        // Build hash string as per EasyPaisa docs
        $hashStr = $amount . '&' . $expiryDateTime . '&' . $orderRefNumber . '&' .
            $returnUrl . '&' . $this->storeId . '&' . $txnDateTime . '&' .
            config('services.easypaisa.env', 'sandbox');

        $hash = strtoupper(hash_hmac('sha256', $hashStr, $this->hashKey));

        // Return form data — frontend will POST this to EasyPaisa
        return response()->json([
            'endpoint'       => $this->endpoint,
            'storeId'        => $this->storeId,
            'amount'         => $amount,
            'orderRefNumber' => $orderRefNumber,
            'txnDateTime'    => $txnDateTime,
            'expiryDateTime' => $expiryDateTime,
            'returnUrl'      => $returnUrl,
            'hash'           => $hash,
        ]);
    }

    /**
     * Callback from EasyPaisa after payment.
     * GET/POST /api/payments/easypaisa/callback
     */
    public function callback(Request $request)
    {
        Log::info('EasyPaisa callback', $request->all());

        $responseCode   = $request->input('responseCode');
        $orderRefNumber = $request->input('orderRefNumber');

        if ($responseCode === '0000') {
            $order = Order::where('order_number', $orderRefNumber)->first();
            if ($order) {
                $order->update(['payment_status' => 'paid']);
            }
            return redirect('/orders?payment=success');
        }

        return redirect('/orders?payment=failed&reason=' . urlencode($request->input('responseDesc', 'Payment failed')));
    }
}
