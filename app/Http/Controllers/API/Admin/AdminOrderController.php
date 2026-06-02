<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Mail\OrderStatusUpdated;
use App\Models\Order;
use App\Models\OrderTracking;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class AdminOrderController extends Controller
{
    public function __construct(private NotificationService $notificationService) {}

    public function index(Request $request)
    {
        $orders = Order::with(['user', 'items'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(15);

        return response()->json($orders);
    }

    public function show(Order $order)
    {
        return response()->json($order->load(['user', 'items']));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled',
        ]);

        // Generate unique ack token when marking delivered (only once)
        if ($request->status === 'delivered' && !$order->ack_token) {
            $order->generateAckToken();
            $order->refresh();
        }

        $order->update(['status' => $request->status]);

        $trackingMap = [
            'pending'   => ['title' => 'Order Placed',      'description' => null],
            'confirmed' => ['title' => 'Order Confirmed',   'description' => 'Your order has been confirmed and will be prepared shortly.'],
            'processing'=> ['title' => 'Being Prepared',    'description' => 'Your order is being packed carefully.'],
            'shipped'   => ['title' => 'Order Shipped',     'description' => 'Your order is on the way! Our delivery partner will contact you.'],
            'delivered' => ['title' => 'Delivered',         'description' => 'Your order has been delivered successfully.'],
            'cancelled' => ['title' => 'Order Cancelled',   'description' => 'Your order has been cancelled.'],
        ];

        if (isset($trackingMap[$request->status])) {
            $entry = $trackingMap[$request->status];
            OrderTracking::create([
                'order_id'    => $order->id,
                'status'      => $request->status,
                'title'       => $entry['title'],
                'description' => $entry['description'],
            ]);
        }

        $order->load('items');

        $email = $order->user?->email ?? $order->guest_email;
        if ($email) {
            try {
                Mail::to($email)->send(new OrderStatusUpdated($order));
            } catch (\Exception $e) {
                \Log::warning("Order status email failed for order {$order->order_number}: " . $e->getMessage());
            }
        }

        // Send WhatsApp notification to the customer's shipping phone
        if ($order->shipping_phone) {
            $this->notificationService->sendWhatsApp(
                $order->shipping_phone,
                "Your order {$order->order_number} status has been updated to: {$request->status}"
            );
        }

        return response()->json($order);
    }
}
