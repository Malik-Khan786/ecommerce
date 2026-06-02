<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function validate(Request $request)
    {
        $request->validate([
            'code'        => 'required|string',
            'cart_total'  => 'required|numeric|min:0',
        ]);

        $coupon = Coupon::where('code', strtoupper($request->code))->first();

        if (!$coupon || !$coupon->isValid($request->cart_total)) {
            return response()->json(['message' => 'Invalid or expired coupon code.'], 422);
        }

        $discount = $coupon->calculateDiscount($request->cart_total);

        return response()->json([
            'code'        => $coupon->code,
            'description' => $coupon->description,
            'type'        => $coupon->type,
            'value'       => $coupon->value,
            'discount'    => $discount,
        ]);
    }

    // Admin CRUD
    public function index()
    {
        return response()->json(Coupon::latest()->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code'             => 'required|string|max:50|unique:coupons',
            'description'      => 'nullable|string|max:255',
            'type'             => 'required|in:percent,fixed',
            'value'            => 'required|numeric|min:1',
            'min_order_amount' => 'nullable|numeric|min:0',
            'usage_limit'      => 'nullable|integer|min:1',
            'is_active'        => 'boolean',
            'expires_at'       => 'nullable|date|after:today',
        ]);

        $data['code'] = strtoupper($data['code']);
        return response()->json(Coupon::create($data), 201);
    }

    public function update(Request $request, Coupon $coupon)
    {
        $data = $request->validate([
            'description'      => 'nullable|string|max:255',
            'type'             => 'in:percent,fixed',
            'value'            => 'numeric|min:1',
            'min_order_amount' => 'nullable|numeric|min:0',
            'usage_limit'      => 'nullable|integer|min:1',
            'is_active'        => 'boolean',
            'expires_at'       => 'nullable|date',
        ]);

        $coupon->update($data);
        return response()->json($coupon);
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();
        return response()->json(['message' => 'Coupon deleted.']);
    }
}
