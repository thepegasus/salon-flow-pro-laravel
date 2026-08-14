<?php

namespace Tests\Feature\Expenses;

use App\Models\Expense;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ActsAsTenant;
use Tests\TestCase;

class UpdateExpenseTest extends TestCase
{
    use ActsAsTenant, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpTenant();
        $this->seed(PermissionSeeder::class);
    }

    public function test_owner_can_update_an_expense(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');
        $expense = Expense::factory()->create(['tenant_id' => $this->tenant->id, 'created_by' => $owner->id, 'description' => 'Original']);

        $response = $this->actingAs($owner)->putToTenant("/expenses/{$expense->id}", [
            'description' => 'Updated description',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('expenses', ['id' => $expense->id, 'description' => 'Updated description']);
    }

    public function test_marking_an_expense_non_recurring_clears_its_interval(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');
        $expense = Expense::factory()->recurring('weekly')->create(['tenant_id' => $this->tenant->id, 'created_by' => $owner->id]);

        $response = $this->actingAs($owner)->putToTenant("/expenses/{$expense->id}", [
            'is_recurring' => false,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'is_recurring' => false,
            'recurrence_interval' => null,
        ]);
    }

    public function test_validation_rejects_invalid_recurrence_interval(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');
        $expense = Expense::factory()->create(['tenant_id' => $this->tenant->id, 'created_by' => $owner->id]);

        $response = $this->actingAs($owner)->putToTenant("/expenses/{$expense->id}", [
            'is_recurring' => true,
            'recurrence_interval' => 'daily',
        ]);

        $response->assertSessionHasErrors('recurrence_interval');
    }
}
