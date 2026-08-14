<?php

namespace Tests\Unit\Billing;

use App\Models\Bill;
use App\Models\Client;
use App\Models\Tenant;
use App\Models\User;
use App\Services\BillingService;
use App\Services\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class BillingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_manual_bill_computes_subtotal_tax_and_total_correctly(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantContext::class)->set($tenant);
        $client = Client::factory()->create(['tenant_id' => $tenant->id]);
        $user = User::factory()->for($tenant)->create();

        $bill = app(BillingService::class)->createManualBill($client->id, $user->id, [
            ['description' => 'Hair Color', 'unit_price' => 1000, 'tax_rate' => 18],
        ]);

        $this->assertSame('1000.00', (string) $bill->subtotal);
        $this->assertSame('180.00', (string) $bill->tax_amount);
        $this->assertSame('1180.00', (string) $bill->total);
    }

    public function test_bill_numbers_are_sequential_per_tenant(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantContext::class)->set($tenant);
        $client = Client::factory()->create(['tenant_id' => $tenant->id]);
        $user = User::factory()->for($tenant)->create();

        $first = app(BillingService::class)->createManualBill($client->id, $user->id, [
            ['description' => 'Item A', 'unit_price' => 100],
        ]);
        $second = app(BillingService::class)->createManualBill($client->id, $user->id, [
            ['description' => 'Item B', 'unit_price' => 100],
        ]);

        $this->assertSame($first->bill_number + 1, $second->bill_number);
    }

    public function test_create_bill_rejects_empty_line_items(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantContext::class)->set($tenant);
        $client = Client::factory()->create(['tenant_id' => $tenant->id]);
        $user = User::factory()->for($tenant)->create();

        $this->expectException(InvalidArgumentException::class);

        app(BillingService::class)->createManualBill($client->id, $user->id, []);
    }

    public function test_record_payments_supports_split_across_methods_and_marks_paid_when_fully_covered(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantContext::class)->set($tenant);
        $client = Client::factory()->create(['tenant_id' => $tenant->id]);
        $user = User::factory()->for($tenant)->create();

        $bill = app(BillingService::class)->createManualBill($client->id, $user->id, [
            ['description' => 'Facial', 'unit_price' => 1000, 'tax_rate' => 18],
        ]);

        $updated = app(BillingService::class)->recordPayments($bill, [
            ['method' => 'cash', 'amount' => 700],
            ['method' => 'card', 'amount' => 480],
        ], $user->id);

        $this->assertSame(Bill::StatusPaid, $updated->status);
        $this->assertSame('1180.00', (string) $updated->amount_paid);
        $this->assertSame(2, $updated->payments()->count());
    }

    public function test_record_partial_payment_marks_bill_as_partial(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantContext::class)->set($tenant);
        $client = Client::factory()->create(['tenant_id' => $tenant->id]);
        $user = User::factory()->for($tenant)->create();

        $bill = app(BillingService::class)->createManualBill($client->id, $user->id, [
            ['description' => 'Facial', 'unit_price' => 1000, 'tax_rate' => 18],
        ]);

        $updated = app(BillingService::class)->recordPayments($bill, [
            ['method' => 'cash', 'amount' => 500],
        ], $user->id);

        $this->assertSame(Bill::StatusPartial, $updated->status);
    }

    public function test_refund_rejects_amount_greater_than_paid(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantContext::class)->set($tenant);
        $client = Client::factory()->create(['tenant_id' => $tenant->id]);
        $user = User::factory()->for($tenant)->create();

        $bill = app(BillingService::class)->createManualBill($client->id, $user->id, [
            ['description' => 'Facial', 'unit_price' => 1000, 'tax_rate' => 18],
        ]);
        app(BillingService::class)->recordPayments($bill, [['method' => 'cash', 'amount' => 500]], $user->id);

        $this->expectException(InvalidArgumentException::class);

        app(BillingService::class)->refund($bill, 600, 'Client complaint', $user->id);
    }

    public function test_refund_within_paid_amount_succeeds_and_tracks_reason(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantContext::class)->set($tenant);
        $client = Client::factory()->create(['tenant_id' => $tenant->id]);
        $user = User::factory()->for($tenant)->create();

        $bill = app(BillingService::class)->createManualBill($client->id, $user->id, [
            ['description' => 'Facial', 'unit_price' => 1000, 'tax_rate' => 18],
        ]);
        app(BillingService::class)->recordPayments($bill, [['method' => 'cash', 'amount' => 1180]], $user->id);

        $refunded = app(BillingService::class)->refund($bill, 200, 'Service not completed', $user->id);

        $this->assertSame('200.00', (string) $refunded->amount_refunded);
        $this->assertDatabaseHas('bill_refunds', ['bill_id' => $bill->id, 'reason' => 'Service not completed']);
    }
}
