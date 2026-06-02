<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\FlashSale;
use Illuminate\Http\Request;

class FlashSaleController extends Controller
{
    public function index()
    {
        return response()->json(FlashSale::active()->with('product')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id'       => 'required|exists:products,id',
            'discount_percent' => 'required|numeric|min:1|max:99',
            'starts_at'        => 'required|date',
            'ends_at'          => 'required|date|after:starts_at',
            'is_active'        => 'boolean',
        ]);
        return response()->json(FlashSale::create($data), 201);
    }

    public function update(Request $request, FlashSale $flashSale)
    {
        $data = $request->validate([
            'product_id'       => 'sometimes|exists:products,id',
            'discount_percent' => 'sometimes|numeric|min:1|max:99',
            'starts_at'        => 'sometimes|date',
            'ends_at'          => 'sometimes|date|after:starts_at',
            'is_active'        => 'boolean',
        ]);
        $flashSale->update($data);
        return response()->json($flashSale->load('product'));
    }

    public function destroy(FlashSale $flashSale)
    {
        $flashSale->delete();
        return response()->json(['message' => 'Deleted.']);
    }

    public function adminIndex()
    {
        return response()->json(FlashSale::with('product')->latest()->get());
    }
}
