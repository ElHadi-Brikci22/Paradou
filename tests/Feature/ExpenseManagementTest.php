<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_store_and_delete_expense()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->actingAs($admin);

        // 1. Store expense
        $response = $this->post(route('admin.expenses.store'), [
            'amount' => 35000,
            'category' => 'Salaire employé',
            'expense_date' => now()->toDateString(),
            'notes' => 'Paiement salaire employé Karim',
            'user_id' => $admin->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('expenses', [
            'amount' => 35000,
            'category' => 'Salaire employé',
            'notes' => 'Paiement salaire employé Karim',
        ]);

        $expense = Expense::first();

        // 2. Dashboard displays expense
        $dashResponse = $this->get(route('admin.dashboard'));
        $dashResponse->assertStatus(200);
        $dashResponse->assertSee('Charges & Dépenses', false);
        $dashResponse->assertSee('Salaire employé');
        $dashResponse->assertSee('35 000 DA');

        // 3. Delete expense
        $delResponse = $this->delete(route('admin.expenses.destroy', $expense->id));
        $delResponse->assertRedirect();
        $delResponse->assertSessionHas('success');

        $this->assertDatabaseMissing('expenses', [
            'id' => $expense->id,
        ]);
    }

    public function test_custom_category_can_be_stored()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->actingAs($admin);

        $response = $this->post(route('admin.expenses.store'), [
            'amount' => 5000,
            'category' => '__custom__',
            'custom_category' => 'Achat fer à repasser',
            'expense_date' => now()->toDateString(),
            'notes' => 'Urgent pour la caisse',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('expenses', [
            'amount' => 5000,
            'category' => 'Achat fer à repasser',
        ]);
    }
}
