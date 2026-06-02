<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\EmailLead;
use Illuminate\Http\Request;

class EmailLeadController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'email'      => 'required|email|max:255',
            'name'       => 'nullable|string|max:255',
            'product_id' => 'nullable|exists:products,id',
            'browser'    => 'nullable|string|max:100',
            'device'     => 'nullable|string|max:100',
        ]);

        // Don't duplicate — update if same email + product
        EmailLead::updateOrCreate(
            ['email' => $request->email, 'product_id' => $request->product_id],
            [
                'name'       => $request->name,
                'source'     => 'product_popup',
                'ip_address' => $request->ip(),
                'browser'    => $request->browser,
                'device'     => $request->device,
            ]
        );

        return response()->json(['message' => 'Thank you! We\'ll be in touch.']);
    }
}
