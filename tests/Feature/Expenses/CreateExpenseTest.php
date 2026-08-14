<?php

namespace Tests\Feature\Expenses;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\ActsAsTenant;
use Tests\TestCase;

class CreateExpenseTest extends TestCase
{
    use ActsAsTenant, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpTenant();
        $this->seed(PermissionSeeder::class);
    }

    public function test_owner_can_create_an_expense_via_subdomain(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');
        $category = ExpenseCategory::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($owner)->postToTenant('/expenses', [
            'description' => 'August rent',
            'category_id' => $category->id,
            'amount' => 5000,
            'expense_date' => '2026-08-01',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('expenses', [
            'description' => 'August rent',
            'amount' => 5000,
            'category_id' => $category->id,
            'tenant_id' => $this->tenant->id,
            'created_by' => $owner->id,
        ]);
    }

    public function test_owner_can_create_an_expense_via_slug_path(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');

        $response = $this->actingAs($owner)->post($this->bySlugUrl('/expenses'), [
            'description' => 'August rent',
            'amount' => 5000,
            'expense_date' => '2026-08-01',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('expenses', [
            'description' => 'August rent',
            'tenant_id' => $this->tenant->id,
        ]);
    }

    public function test_creating_a_recurring_expense_persists_the_interval(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');

        $response = $this->actingAs($owner)->postToTenant('/expenses', [
            'description' => 'Monthly software subscription',
            'amount' => 999,
            'is_recurring' => true,
            'recurrence_interval' => 'monthly',
            'expense_date' => '2026-08-01',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('expenses', [
            'description' => 'Monthly software subscription',
            'is_recurring' => true,
            'recurrence_interval' => 'monthly',
        ]);
    }

    public function test_uploading_a_receipt_stores_it_and_links_the_path(): void
    {
        Storage::fake('local');

        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');

        $receipt = UploadedFile::fake()->create('receipt.pdf', 200, 'application/pdf');

        $response = $this->actingAs($owner)->postToTenant('/expenses', [
            'description' => 'Supplies with receipt',
            'amount' => 250,
            'expense_date' => '2026-08-01',
            'receipt' => $receipt,
        ]);

        $response->assertRedirect();

        $expense = Expense::where('description', 'Supplies with receipt')->firstOrFail();

        $this->assertNotNull($expense->receipt_path);
        Storage::disk('local')->assertExists($expense->receipt_path);
    }

    public function test_validation_rejects_missing_description(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');

        $response = $this->actingAs($owner)->postToTenant('/expenses', [
            'amount' => 100,
            'expense_date' => '2026-08-01',
        ]);

        $response->assertSessionHasErrors('description');
    }

    public function test_validation_rejects_zero_amount(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');

        $response = $this->actingAs($owner)->postToTenant('/expenses', [
            'description' => 'Bad amount',
            'amount' => 0,
            'expense_date' => '2026-08-01',
        ]);

        $response->assertSessionHasErrors('amount');
    }

    public function test_validation_requires_recurrence_interval_when_recurring(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');

        $response = $this->actingAs($owner)->postToTenant('/expenses', [
            'description' => 'Missing interval',
            'amount' => 100,
            'is_recurring' => true,
            'expense_date' => '2026-08-01',
        ]);

        $response->assertSessionHasErrors('recurrence_interval');
    }
}
