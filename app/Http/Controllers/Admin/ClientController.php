<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Client;

class ClientController extends Controller
{
    /**
     * Display a listing of clients.
     */
    public function index(Request $request)
    {
        $search = $request->input('search', '');

        $query = Client::query();

        if (!empty(trim($search))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('code', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%")
                  ->orWhere('address', 'LIKE', "%{$search}%");
            });
        }

        // Paginate clients
        $clients = $query->orderBy('name', 'asc')->paginate(15)->withQueryString();

        return view('admin.clients.index', compact('clients', 'search'));
    }

    /**
     * Store a newly created client in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:255',
            'discount_percent' => 'nullable|integer|min:0|max:100',
            'credit' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string'
        ]);

        // Generate next code (excluding GUEST)
        $lastClient = Client::where('code', '!=', 'GUEST')->orderBy('id', 'desc')->first();
        $nextCode = $lastClient ? str_pad(intval($lastClient->code) + 1, 6, '0', STR_PAD_LEFT) : '000001';

        Client::create([
            'code' => $nextCode,
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'address' => $validated['address'] ?? null,
            'discount_percent' => intval($validated['discount_percent'] ?? 0),
            'credit' => floatval($validated['credit'] ?? 0.00),
            'remarks' => $validated['remarks'] ?? null
        ]);

        return redirect()->route('admin.clients.index')->with('success', 'Client créé avec succès !');
    }

    /**
     * Update the specified client in storage.
     */
    public function update(Request $request, $id)
    {
        $client = Client::findOrFail($id);

        // Prevent modification of GUEST client code
        if ($client->code === 'GUEST') {
            return redirect()->route('admin.clients.index')->withErrors(['error' => 'Le client de passage ne peut pas être modifié.']);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:255',
            'discount_percent' => 'nullable|integer|min:0|max:100',
            'credit' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string'
        ]);

        $client->update([
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'address' => $validated['address'] ?? null,
            'discount_percent' => intval($validated['discount_percent'] ?? 0),
            'credit' => floatval($validated['credit'] ?? 0.00),
            'remarks' => $validated['remarks'] ?? null
        ]);

        return redirect()->route('admin.clients.index')->with('success', 'Client mis à jour avec succès !');
    }

    /**
     * Remove the specified client from storage.
     */
    public function destroy($id)
    {
        $client = Client::findOrFail($id);

        if ($client->code === 'GUEST') {
            return redirect()->route('admin.clients.index')->withErrors(['error' => 'Le client de passage ne peut pas être supprimé.']);
        }

        // Check if there are active orders
        if ($client->orders()->count() > 0) {
            return redirect()->route('admin.clients.index')->withErrors(['error' => 'Impossible de supprimer ce client car il possède des commandes enregistrées dans le système.']);
        }

        $client->delete();

        return redirect()->route('admin.clients.index')->with('success', 'Client supprimé avec succès !');
    }
}
