<?php

namespace Tests\Feature\Expenses;

use App\Models\Expense;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ActsAsTenant;
use Tests\TestCase;

class ExpensesAccessTest extends TestCase
{
    use ActsAsTenant, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpTenant();
        $this->seed(PermissionSeeder::class);
    }

    public function test_owner_can_access_expenses_index(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');

        $response = $this->actingAs($owner)->getFromTenant('/expenses');

        $response->assertOk();
    }

    public function test_manager_can_access_expenses_index(): void
    {
        $manager = User::factory()->for($this->tenant)->create();
        $manager->assignRole('Manager');

        $response = $this->actingAs($manager)->getFromTenant('/expenses');

        $response->assertOk();
    }

    public function test_manager_can_create_an_expense(): void
    {
        $manager = User::factory()->for($this->tenant)->create();
        $manager->assignRole('Manager');

        $response = $this->actingAs($manager)->postToTenant('/expenses', [
            'description' => 'Office supplies',
            'amount' => 150,
            'expense_date' => '2026-08-01',
        ]);

        $response->assertRedirect();
    }

    public function test_front_desk_cannot_view_expenses_index(): void
    {
        $frontDesk = User::factory()->for($this->tenant)->create();
        $frontDesk->assignRole('FrontDesk');

        $response = $this->actingAs($frontDesk)->getFromTenant('/expenses');

        $response->assertForbidden();
    }

    public function test_front_desk_cannot_create_an_expense(): void
    {
        $frontDesk = User::factory()->for($this->tenant)->create();
        $frontDesk->assignRole('FrontDesk');

        $response = $this->actingAs($frontDesk)->getFromTenant('/expenses/create');

        $response->assertForbidden();
    }

    public function test_stylist_cannot_view_expenses_index(): void
    {
        $stylist = User::factory()->for($this->tenant)->create();
        $stylist->assignRole('Stylist');

        $response = $this->actingAs($stylist)->getFromTenant('/expenses');

        $response->assertForbidden();
    }

    public function test_stylist_cannot_edit_an_expense(): void
    {
        $stylist = User::factory()->for($this->tenant)->create();
        $stylist->assignRole('Stylist');
        $expense = Expense::factory()->create(['tenant_id' => $this->tenant->id, 'created_by' => $stylist->id]);

        $response = $this->actingAs($stylist)->getFromTenant("/expenses/{$expense->id}/edit");

        $response->assertForbidden();
    }

    public function test_stylist_cannot_delete_an_expense(): void
    {
        $stylist = User::factory()->for($this->tenant)->create();
        $stylist->assignRole('Stylist');
        $expense = Expense::factory()->create(['tenant_id' => $this->tenant->id, 'created_by' => $stylist->id]);

        $response = $this->actingAs($stylist)->deleteFromTenant("/expenses/{$expense->id}");

        $response->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->getFromTenant('/expenses');

        $response->assertRedirect('/login');
    }
}
