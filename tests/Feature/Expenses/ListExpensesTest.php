<?php

namespace Tests\Feature\Expenses;

use App\Models\Expense;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ActsAsTenant;
use Tests\TestCase;

class ListExpensesTest extends TestCase
{
    use ActsAsTenant, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpTenant();
        $this->seed(PermissionSeeder::class);
    }

    public function test_index_lists_expenses_for_the_current_month_by_default(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');
        Expense::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $owner->id,
            'description' => 'This month expense',
            'expense_date' => now()->format('Y-m-d'),
        ]);

        $response = $this->actingAs($owner)->getFromTenant('/expenses');

        $response->assertOk();
        $response->assertSee('This month expense');
    }

    public function test_month_filter_excludes_expenses_outside_the_requested_month(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');
        Expense::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $owner->id,
            'description' => 'August expense',
            'expense_date' => '2026-08-15',
        ]);
        Expense::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $owner->id,
            'description' => 'September expense',
            'expense_date' => '2026-09-15',
        ]);

        $response = $this->actingAs($owner)->getFromTenant('/expenses?month=2026-08');

        $response->assertOk();
        $response->assertSee('August expense');
        $response->assertDontSee('September expense');
    }

    public function test_index_is_accessible_via_slug_path(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');
        Expense::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $owner->id,
            'description' => 'Slug path expense',
            'expense_date' => now()->format('Y-m-d'),
        ]);

        $response = $this->actingAs($owner)->get($this->bySlugUrl('/expenses'));

        $response->assertOk();
        $response->assertSee('Slug path expense');
    }
}
