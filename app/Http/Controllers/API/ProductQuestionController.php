<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductQuestion;
use Illuminate\Http\Request;

class ProductQuestionController extends Controller
{
    public function index(string $slug)
    {
        $product = Product::where('slug', $slug)->firstOrFail();

        $questions = ProductQuestion::with('user')
            ->where('product_id', $product->id)
            ->where('is_approved', true)
            ->latest()
            ->get();

        return response()->json($questions);
    }

    public function store(Request $request, string $slug)
    {
        $product = Product::where('slug', $slug)->firstOrFail();

        // Works with or without auth middleware — resolves token if present
        $user = auth('sanctum')->user();

        $guestNameRules = $user
            ? ['nullable', 'string', 'max:100']
            : ['required', 'string', 'max:100'];

        $data = $request->validate([
            'question'   => ['required', 'string', 'max:500'],
            'guest_name' => $guestNameRules,
            'guest_email'=> ['nullable', 'email', 'max:255'],
        ]);

        $question = ProductQuestion::create([
            'product_id'  => $product->id,
            'user_id'     => $user?->id,
            'guest_name'  => $user ? null : ($data['guest_name'] ?? null),
            'guest_email' => $user ? null : ($data['guest_email'] ?? null),
            'question'    => $data['question'],
        ]);

        return response()->json($question->load('user'), 201);
    }

    public function adminIndex(Request $request)
    {
        $questions = ProductQuestion::with(['user', 'product:id,name,slug,thumbnail'])
            ->when($request->is_answered !== null && $request->is_answered !== '',
                fn($q) => $q->where('is_answered', (bool) $request->is_answered))
            ->latest()
            ->paginate(15);

        return response()->json($questions);
    }

    public function answer(Request $request, ProductQuestion $question)
    {
        $data = $request->validate([
            'answer' => ['required', 'string'],
        ]);

        $question->update([
            'answer'      => $data['answer'],
            'is_answered' => true,
        ]);

        return response()->json($question);
    }
}
