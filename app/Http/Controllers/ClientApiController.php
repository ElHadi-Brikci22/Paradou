<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;

class ClientApiController extends Controller
{
    /**
     * Search for clients dynamically via AJAX.
     */
    public function search(Request $request)
    {
        $query = $request->input('q', '');

        if (empty(trim($query))) {
            return response()->json([]);
        }

        $clients = Client::where('name', 'LIKE', "%{$query}%")
            ->orWhere('code', 'LIKE', "%{$query}%")
            ->orWhere('phone', 'LIKE', "%{$query}%")
            ->orderBy('name', 'asc')
            ->limit(10)
            ->get();

        return response()->json($clients);
    }

    /**
     * Create a new client quickly from checkout.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'discount_percent' => 'nullable|integer|min:0|max:100',
            'address' => 'nullable|string|max:255',
            'remarks' => 'nullable|string'
        ]);

        // Generate next code
        $lastClient = Client::orderBy('id', 'desc')->first();
        $nextCode = $lastClient ? str_pad(intval($lastClient->code) + 1, 6, '0', STR_PAD_LEFT) : '000001';

        $client = Client::create([
            'code' => $nextCode,
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'remarks' => $validated['remarks'] ?? null,
            'discount_percent' => intval($validated['discount_percent'] ?? 0),
            'credit' => 0.00
        ]);

        return response()->json([
            'success' => true,
            'client' => $client
        ]);
    }
}
