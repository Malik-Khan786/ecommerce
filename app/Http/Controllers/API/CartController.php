<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CartController extends Controller
{
    private function guestToken(Request $request): string
    {
        return $request->header('X-Cart-Token') ?: '';
    }

    private function getCartQuery(Request $request)
    {
        if ($request->user()) {
            return Cart::where('user_id', $request->user()->id);
        }
        return Cart::where('session_id', $this->guestToken($request));
    }

    public function index(Request $request)
    {
        $items = $this->getCartQuery($request)->with('product')->get();
        return response()->json($items);
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);

        $cartData = $request->user()
            ? ['user_id' => $request->user()->id, 'product_id' => $request->product_id]
            : ['session_id' => $this->guestToken($request), 'product_id' => $request->product_id];

        $existing = Cart::where($cartData)->first();

        if ($existing) {
            $newQty = min($existing->quantity + $request->quantity, $product->stock);
            $existing->update(['quantity' => $newQty]);
            return response()->json($existing->load('product'));
        }

        $cartData['quantity'] = min($request->quantity, $product->stock);
        $item = Cart::create($cartData);

        return response()->json($item->load('product'), 201);
    }

    public function update(Request $request, Cart $cart)
    {
        $request->validate(['quantity' => 'required|integer|min:1']);
        $cart->update(['quantity' => min($request->quantity, $cart->product->stock)]);
        return response()->json($cart->load('product'));
    }

    public function remove(Cart $cart)
    {
        $cart->delete();
        return response()->json(['message' => 'Item removed from cart.']);
    }

    public function clear(Request $request)
    {
        $this->getCartQuery($request)->delete();
        return response()->json(['message' => 'Cart cleared.']);
    }
}
