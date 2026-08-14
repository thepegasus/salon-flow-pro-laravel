<?php

namespace Tests\Feature\Expenses;

use App\Models\Expense;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ActsAsTenant;
use Tests\TestCase;

class DeleteExpenseTest extends TestCase
{
    use ActsAsTenant, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpTenant();
        $this->seed(PermissionSeeder::class);
    }

    public function test_owner_can_delete_an_expense(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');
        $expense = Expense::factory()->create(['tenant_id' => $this->tenant->id, 'created_by' => $owner->id]);

        $response = $this->actingAs($owner)->deleteFromTenant("/expenses/{$expense->id}");

        $response->assertRedirect();
        $this->assertSoftDeleted('expenses', ['id' => $expense->id]);
    }

    public function test_deleted_expense_no_longer_appears_in_the_index(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');
        $expense = Expense::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $owner->id,
            'description' => 'Deletable expense',
            'expense_date' => now()->format('Y-m-d'),
        ]);

        $this->actingAs($owner)->deleteFromTenant("/expenses/{$expense->id}");

        $response = $this->actingAs($owner)->getFromTenant('/expenses');

        $response->assertDontSee('Deletable expense');
    }
}
