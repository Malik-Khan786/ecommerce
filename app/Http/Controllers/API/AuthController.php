<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Cart;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone'    => 'nullable|string|max:20',
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'phone'    => $data['phone'] ?? null,
        ]);

        $cartToken = $request->header('X-Cart-Token');
        if ($cartToken) {
            $this->mergeGuestCart($cartToken, $user->id);
        }

        return response()->json([
            'user'  => $user,
            'token' => $user->createToken('auth_token')->plainTextToken,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        $user      = Auth::user();
        $cartToken = $request->header('X-Cart-Token');

        if ($cartToken) {
            $this->mergeGuestCart($cartToken, $user->id);
        }

        return response()->json([
            'user'  => $user,
            'token' => $user->createToken('auth_token')->plainTextToken,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully.']);
    }

    public function profile(Request $request)
    {
        return response()->json($request->user());
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'name'  => 'sometimes|string|max:255',
            'phone' => 'sometimes|nullable|string|max:20',
        ]);
        $user->update($data);
        return response()->json($user);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();
        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Current password is incorrect.'],
            ]);
        }

        $user->update(['password' => Hash::make($request->password)]);
        return response()->json(['message' => 'Password changed successfully.']);
    }

    public function addresses(Request $request)
    {
        return response()->json($request->user()->addresses()->orderByDesc('is_default')->get());
    }

    public function storeAddress(Request $request)
    {
        $data = $request->validate([
            'label'        => 'nullable|string|max:50',
            'full_name'    => 'required|string|max:255',
            'phone'        => 'required|string|max:20',
            'address_line1'=> 'required|string|max:500',
            'address_line2'=> 'nullable|string|max:500',
            'city'         => 'required|string|max:100',
            'state'        => 'nullable|string|max:100',
            'postal_code'  => 'nullable|string|max:20',
            'country'      => 'nullable|string|max:100',
            'is_default'   => 'boolean',
        ]);

        $data['user_id'] = $request->user()->id;

        if (!empty($data['is_default'])) {
            Address::where('user_id', $request->user()->id)->update(['is_default' => false]);
        }

        return response()->json(Address::create($data), 201);
    }

    public function updateAddress(Request $request, Address $address)
    {
        abort_if($address->user_id !== $request->user()->id, 403);

        $data = $request->validate([
            'label'        => 'nullable|string|max:50',
            'full_name'    => 'sometimes|string|max:255',
            'phone'        => 'sometimes|string|max:20',
            'address_line1'=> 'sometimes|string|max:500',
            'address_line2'=> 'nullable|string|max:500',
            'city'         => 'sometimes|string|max:100',
            'state'        => 'nullable|string|max:100',
            'postal_code'  => 'nullable|string|max:20',
            'is_default'   => 'boolean',
        ]);

        if (!empty($data['is_default'])) {
            Address::where('user_id', $request->user()->id)->update(['is_default' => false]);
        }

        $address->update($data);
        return response()->json($address);
    }

    public function destroyAddress(Request $request, Address $address)
    {
        abort_if($address->user_id !== $request->user()->id, 403);
        $address->delete();
        return response()->json(['message' => 'Address deleted.']);
    }

    private function mergeGuestCart(string $guestToken, int $userId): void
    {
        $guestItems = Cart::where('session_id', $guestToken)->get();

        foreach ($guestItems as $guestItem) {
            $existing = Cart::where('user_id', $userId)
                ->where('product_id', $guestItem->product_id)
                ->first();

            if ($existing) {
                $existing->increment('quantity', $guestItem->quantity);
                $guestItem->delete();
            } else {
                $guestItem->update(['user_id' => $userId, 'session_id' => null]);
            }
        }
    }
}
