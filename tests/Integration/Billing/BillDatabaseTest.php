<?php

namespace Tests\Integration\Billing;

use App\Models\Bill;
use App\Models\Tenant;
use App\Services\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillDatabaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_scope_excludes_bills_from_other_tenants(): void
    {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();

        Bill::factory()->create(['tenant_id' => $tenantA->id]);
        Bill::factory()->create(['tenant_id' => $tenantB->id]);

        app(TenantContext::class)->set($tenantA);

        $this->assertSame(1, Bill::count());
    }

    public function test_voiding_a_bill_soft_deletes_neither_the_bill_nor_its_payments(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantContext::class)->set($tenant);

        $bill = Bill::factory()->create(['tenant_id' => $tenant->id]);
        $bill->payments()->create(['tenant_id' => $tenant->id, 'method' => 'cash', 'amount' => 500]);

        $bill->update(['status' => Bill::StatusVoid]);

        $this->assertDatabaseHas('bills', ['id' => $bill->id, 'status' => 'void']);
        $this->assertDatabaseHas('bill_payments', ['bill_id' => $bill->id]);
    }
}
