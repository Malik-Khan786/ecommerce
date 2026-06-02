<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailLead;
use Illuminate\Http\Request;

class AdminLeadsController extends Controller
{
    public function index(Request $request)
    {
        $leads = EmailLead::with('product:id,name,thumbnail')
            ->when($request->search, fn($q) => $q->where('email', 'like', "%{$request->search}%")
                ->orWhere('name', 'like', "%{$request->search}%"))
            ->when($request->contacted !== null, fn($q) => $q->where('is_contacted', $request->contacted))
            ->latest()
            ->paginate(20);

        return response()->json($leads);
    }

    public function markContacted(Request $request, EmailLead $lead)
    {
        $request->validate(['notes' => 'nullable|string']);
        $lead->update([
            'is_contacted' => true,
            'notes'        => $request->notes,
        ]);
        return response()->json($lead);
    }

    public function destroy(EmailLead $lead)
    {
        $lead->delete();
        return response()->json(['message' => 'Deleted.']);
    }

    public function stats()
    {
        return response()->json([
            'total'        => EmailLead::count(),
            'contacted'    => EmailLead::where('is_contacted', true)->count(),
            'not_contacted'=> EmailLead::where('is_contacted', false)->count(),
            'today'        => EmailLead::whereDate('created_at', today())->count(),
        ]);
    }
}
