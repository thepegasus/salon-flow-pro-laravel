<?php

namespace Tests\Integration\Billing;

use App\Models\Client;
use App\Models\Service;
use App\Models\Tenant;
use App\Models\User;
use App\Services\QuickBillService;
use App\Services\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuickBillDatabaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_code_lookup_is_scoped_to_the_active_tenant(): void
    {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();

        Service::factory()->create(['tenant_id' => $tenantA->id, 'code' => '101', 'name' => 'Tenant A service']);
        Service::factory()->create(['tenant_id' => $tenantB->id, 'code' => '101', 'name' => 'Tenant B service']);

        app(TenantContext::class)->set($tenantA);
        $found = app(QuickBillService::class)->findServiceByCode('101');

        $this->assertSame('Tenant A service', $found->name);
    }

    public function test_settling_a_quick_bill_persists_bill_line_items_and_payment_rows(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantContext::class)->set($tenant);
        $service = Service::factory()->create(['tenant_id' => $tenant->id, 'code' => '205', 'name' => 'Global colour', 'price' => 4200]);
        $client = Client::factory()->create(['tenant_id' => $tenant->id]);
        $staff = User::factory()->for($tenant)->create();

        $bill = app(QuickBillService::class)->createAndSettle(['205'], $client->id, 'upi', $staff->id);

        $this->assertDatabaseHas('bill_line_items', [
            'bill_id' => $bill->id,
            'description' => 'Global colour',
        ]);
        $this->assertDatabaseHas('bill_payments', [
            'bill_id' => $bill->id,
            'method' => 'upi',
        ]);
    }
}
