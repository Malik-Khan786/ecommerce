<?php

namespace App\Http\Controllers\API;

use App\Exceptions\InsufficientStockException;
use App\Http\Controllers\Controller;
use App\Mail\OrderConfirmed;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $orders = Order::with('items')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return response()->json($orders);
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        abort_if($order->user_id !== $request->user()->id, 403);
        return response()->json($order->load('items'));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'shipping_name'        => 'required|string|max:255',
            'shipping_phone'       => 'required|string|max:20',
            'shipping_address'     => 'required|string|max:500',
            'shipping_city'        => 'required|string|max:100',
            'shipping_state'       => 'nullable|string|max:100',
            'shipping_postal_code' => 'nullable|string|max:20',
            'payment_method'       => 'required|in:cod,card,jazzcash,easypaisa',
            'coupon_code'          => 'nullable|string|max:50',
            'notes'                => 'nullable|string|max:1000',
        ]);

        $cartItems = $this->resolveCartItems($request);

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Cart is empty.'], 422);
        }

        $subtotal     = $this->calculateSubtotal($cartItems);
        $discount     = $this->resolveCouponDiscount($request->coupon_code, $subtotal);
        $shippingCost = ($subtotal - $discount) >= 2000 ? 0 : 200;
        $total        = $subtotal - $discount + $shippingCost;

        try {
            return DB::transaction(function () use ($request, $cartItems, $subtotal, $discount, $shippingCost, $total) {
                $products = $this->lockAndValidateStock($cartItems);
                $order    = $this->createOrder($request, $subtotal, $discount, $shippingCost, $total);
                $this->createOrderItems($order, $cartItems, $products);
                $cartItems->each->delete();

                // Send confirmation email
                $email = $request->user()?->email;
                if ($email) {
                    Mail::to($email)->later(now()->addSeconds(5), new OrderConfirmed($order->load('items')));
                }

                return response()->json($order->load('items'), 201);
            });
        } catch (InsufficientStockException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function cancel(Request $request, Order $order): JsonResponse
    {
        abort_if($order->user_id !== $request->user()->id, 403);

        if (!in_array($order->status, ['pending', 'confirmed'])) {
            return response()->json(['message' => 'Order cannot be cancelled.'], 422);
        }

        $order->update(['status' => 'cancelled']);
        return response()->json($order);
    }

    private function resolveCartItems(Request $request): Collection
    {
        $guestToken = $request->header('X-Cart-Token') ?: '';

        if ($request->user()) {
            $items = Cart::with('product')->where('user_id', $request->user()->id)->get();
            if ($items->isEmpty() && $guestToken) {
                $items = Cart::with('product')->where('session_id', $guestToken)->get();
            }
            return $items;
        }

        return Cart::with('product')
            ->where('session_id', $guestToken)
            ->get();
    }

    private function calculateSubtotal(Collection $cartItems): float
    {
        return $cartItems->sum(
            fn($item) => ($item->product->sale_price ?? $item->product->price) * $item->quantity
        );
    }

    private function resolveCouponDiscount(?string $code, float $subtotal): float
    {
        if (!$code) {
            return 0;
        }

        $coupon = Coupon::where('code', strtoupper($code))->first();

        if (!$coupon || !$coupon->isValid($subtotal)) {
            return 0;
        }

        $coupon->increment('used_count');
        return $coupon->calculateDiscount($subtotal);
    }

    private function lockAndValidateStock(Collection $cartItems): \Illuminate\Support\Collection
    {
        $products = Product::whereIn('id', $cartItems->pluck('product_id'))
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        foreach ($cartItems as $item) {
            $product = $products[$item->product_id] ?? null;
            if (!$product || $product->stock < $item->quantity) {
                throw new InsufficientStockException(
                    $item->product->name,
                    $product?->stock ?? 0
                );
            }
        }

        return $products;
    }

    private function createOrder(Request $request, float $subtotal, float $discount, float $shippingCost, float $total): Order
    {
        return Order::create([
            'user_id'                  => $request->user()?->id,
            'shipping_name'            => $request->shipping_name,
            'shipping_phone'           => $request->shipping_phone,
            'shipping_address'         => $request->shipping_address,
            'shipping_city'            => $request->shipping_city,
            'shipping_state'           => $request->shipping_state ?? '',
            'shipping_postal_code'     => $request->shipping_postal_code ?? '',
            'shipping_country'         => $request->shipping_country ?? 'Pakistan',
            'payment_method'           => $request->payment_method,
            'subtotal'                 => $subtotal,
            'shipping_cost'            => $shippingCost,
            'discount'                 => $discount,
            'total'                    => $total,
            'notes'                    => $request->notes,
            'estimated_delivery_date'  => $this->calculateEstimatedDelivery($request->payment_method),
        ]);
    }

    private function calculateEstimatedDelivery(string $paymentMethod): \Carbon\Carbon
    {
        // COD gets +5 business days; card/jazzcash/easypaisa get +3 business days
        $businessDays = $paymentMethod === 'cod' ? 5 : 3;
        $date = now();
        $added = 0;
        while ($added < $businessDays) {
            $date->addDay();
            // Skip weekends (Saturday = 6, Sunday = 0)
            if (!in_array($date->dayOfWeek, [\Carbon\Carbon::SATURDAY, \Carbon\Carbon::SUNDAY])) {
                $added++;
            }
        }
        return $date;
    }

    private function createOrderItems(Order $order, Collection $cartItems, \Illuminate\Support\Collection $products): void
    {
        foreach ($cartItems as $item) {
            $product = $products[$item->product_id];
            $price   = $product->sale_price ?? $product->price;
            $order->items()->create([
                'product_id'        => $item->product_id,
                'product_name'      => $product->name,
                'product_thumbnail' => $product->thumbnail,
                'price'             => $price,
                'quantity'          => $item->quantity,
                'subtotal'          => $price * $item->quantity,
            ]);
            $product->decrement('stock', $item->quantity);
        }
    }
}
