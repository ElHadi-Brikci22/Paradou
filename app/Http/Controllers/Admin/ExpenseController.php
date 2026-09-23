<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    /**
     * Store a newly created expense in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'category' => 'required|string|max:100',
            'custom_category' => 'nullable|string|max:100',
            'expense_date' => 'required|date',
            'notes' => 'nullable|string|max:500',
            'user_id' => 'nullable|exists:users,id',
        ]);

        if ($validated['category'] === '__custom__') {
            $validated['category'] = !empty($validated['custom_category']) ? trim($validated['custom_category']) : 'Autre charge';
        }
        unset($validated['custom_category']);

        if (empty($validated['user_id'])) {
            $validated['user_id'] = Auth::id();
        }

        $expense = Expense::create($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Charge enregistrée avec succès.',
                'expense' => $expense->load('user'),
            ]);
        }

        return redirect()->back()->with('success', 'Charge de ' . number_format($expense->amount, 0, '.', ' ') . ' DA enregistrée avec succès.');
    }

    /**
     * Remove the specified expense from storage.
     */
    public function destroy(Request $request, $id)
    {
        $expense = Expense::findOrFail($id);
        $amount = $expense->amount;
        $category = $expense->category;
        
        $expense->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Charge supprimée avec succès.',
            ]);
        }

        return redirect()->back()->with('success', "Charge '{$category}' de " . number_format($amount, 0, '.', ' ') . " DA supprimée avec succès.");
    }
}
