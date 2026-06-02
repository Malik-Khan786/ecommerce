<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class JazzCashController extends Controller
{
    private string $merchantId;
    private string $password;
    private string $integritySalt;
    private string $endpoint;

    public function __construct()
    {
        $this->merchantId    = config('services.jazzcash.merchant_id');
        $this->password      = config('services.jazzcash.password');
        $this->integritySalt = config('services.jazzcash.integrity_salt');
        $this->endpoint      = config('services.jazzcash.endpoint');
    }

    /**
     * Initiate a JazzCash Mobile Wallet payment for an order.
     * POST /api/payments/jazzcash
     */
    public function initiate(Request $request)
    {
        $request->validate([
            'order_uuid'   => 'required|string',
            'mobile_number' => 'required|string|regex:/^03[0-9]{9}$/',
        ]);

        if (!$this->merchantId) {
            return response()->json(['message' => 'JazzCash is not configured yet.'], 503);
        }

        $order = Order::where('uuid', $request->order_uuid)
            ->where('user_id', $request->user()?->id)
            ->firstOrFail();

        $txnDateTime  = now()->format('YmdHis');
        $txnRefNumber = 'T' . $txnDateTime . rand(100, 999);
        $amount       = (int) ($order->total * 100); // Amount in paisas

        $params = [
            'pp_Version'        => '1.1',
            'pp_TxnType'        => 'MWALLET',
            'pp_Language'       => 'EN',
            'pp_MerchantID'     => $this->merchantId,
            'pp_Password'       => $this->password,
            'pp_TxnRefNo'       => $txnRefNumber,
            'pp_Amount'         => $amount,
            'pp_TxnCurrency'    => 'PKR',
            'pp_TxnDateTime'    => $txnDateTime,
            'pp_BillReference'  => $order->order_number,
            'pp_Description'    => 'Payment for ' . $order->order_number,
            'pp_TxnExpiryDateTime' => now()->addHours(1)->format('YmdHis'),
            'pp_ReturnURL'      => url('/api/payments/jazzcash/callback'),
            'pp_MobileNumber'   => '92' . substr($request->mobile_number, 1),
            'pp_CNIC'           => '',
        ];

        $params['pp_SecureHash'] = $this->generateHash($params);

        try {
            $response = Http::timeout(30)->post($this->endpoint, $params);
            $result   = $response->json();

            Log::info('JazzCash initiate', ['order' => $order->order_number, 'result' => $result]);

            $responseCode = $result['pp_ResponseCode'] ?? '';

            if ($responseCode === '000') {
                // Success — update order payment status
                $order->update([
                    'payment_status' => 'paid',
                    'notes'          => ($order->notes ? $order->notes . ' | ' : '') . 'JazzCash TxnRef: ' . $txnRefNumber,
                ]);

                return response()->json([
                    'status'  => 'success',
                    'message' => 'Payment successful!',
                    'txn_ref' => $txnRefNumber,
                ]);
            }

            return response()->json([
                'status'  => 'failed',
                'message' => $result['pp_ResponseMessage'] ?? 'Payment failed. Please try again.',
                'code'    => $responseCode,
            ], 422);

        } catch (\Exception $e) {
            Log::error('JazzCash error: ' . $e->getMessage());
            return response()->json(['message' => 'Payment gateway error. Please try again.'], 500);
        }
    }

    /**
     * Callback from JazzCash after payment.
     * POST /api/payments/jazzcash/callback
     */
    public function callback(Request $request)
    {
        Log::info('JazzCash callback', $request->all());

        $hash         = $request->input('pp_SecureHash');
        $params       = collect($request->all())->except('pp_SecureHash')->toArray();
        $expectedHash = $this->generateHash($params);

        if ($hash !== $expectedHash) {
            Log::warning('JazzCash hash mismatch');
            return redirect('/orders?payment=failed');
        }

        $responseCode = $request->input('pp_ResponseCode');
        $billRef      = $request->input('pp_BillReference'); // order_number

        if ($responseCode === '000') {
            $order = Order::where('order_number', $billRef)->first();
            if ($order) {
                $order->update(['payment_status' => 'paid']);
            }
            return redirect('/orders?payment=success');
        }

        return redirect('/orders?payment=failed');
    }

    private function generateHash(array $params): string
    {
        ksort($params);
        $hashData = $this->integritySalt . '&' . implode('&', array_filter(array_values($params)));
        return hash_hmac('sha256', $hashData, $this->integritySalt);
    }
}
