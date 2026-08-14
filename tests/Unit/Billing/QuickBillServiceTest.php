<?php

namespace Tests\Unit\Billing;

use App\Models\Bill;
use App\Models\Client;
use App\Models\Service;
use App\Models\Tenant;
use App\Models\User;
use App\Services\QuickBillService;
use App\Services\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class QuickBillServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_find_service_by_code_returns_active_service(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantContext::class)->set($tenant);
        $service = Service::factory()->create(['tenant_id' => $tenant->id, 'code' => '101']);

        $found = app(QuickBillService::class)->findServiceByCode('101');

        $this->assertTrue($found->is($service));
    }

    public function test_find_service_by_code_ignores_inactive_services(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantContext::class)->set($tenant);
        Service::factory()->inactive()->create(['tenant_id' => $tenant->id, 'code' => '101']);

        $found = app(QuickBillService::class)->findServiceByCode('101');

        $this->assertNull($found);
    }

    public function test_create_and_settle_builds_bill_from_codes_and_marks_it_paid(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantContext::class)->set($tenant);
        $service = Service::factory()->create(['tenant_id' => $tenant->id, 'code' => '101', 'price' => 500]);
        $client = Client::factory()->create(['tenant_id' => $tenant->id]);
        $staff = User::factory()->for($tenant)->create();

        $bill = app(QuickBillService::class)->createAndSettle(['101'], $client->id, 'cash', $staff->id);

        $this->assertSame(Bill::StatusPaid, $bill->status);
        $this->assertSame($client->id, $bill->client_id);
        $this->assertSame(1, $bill->lineItems()->count());
        $this->assertSame(1, $bill->payments()->where('method', 'cash')->count());
    }

    public function test_create_and_settle_uses_walk_in_client_when_no_client_given(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantContext::class)->set($tenant);
        $service = Service::factory()->create(['tenant_id' => $tenant->id, 'code' => '101']);
        $staff = User::factory()->for($tenant)->create();

        $bill = app(QuickBillService::class)->createAndSettle(['101'], null, 'cash', $staff->id);

        $this->assertSame(QuickBillService::WalkInClientName, $bill->client->name);
    }

    public function test_create_and_settle_reuses_the_same_walk_in_client_across_bills(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantContext::class)->set($tenant);
        $service = Service::factory()->create(['tenant_id' => $tenant->id, 'code' => '101']);
        $staff = User::factory()->for($tenant)->create();

        $first = app(QuickBillService::class)->createAndSettle(['101'], null, 'cash', $staff->id);
        $second = app(QuickBillService::class)->createAndSettle(['101'], null, 'upi', $staff->id);

        $this->assertSame($first->client_id, $second->client_id);
    }

    public function test_create_and_settle_throws_for_unknown_code(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantContext::class)->set($tenant);
        $client = Client::factory()->create(['tenant_id' => $tenant->id]);
        $staff = User::factory()->for($tenant)->create();

        $this->expectException(InvalidArgumentException::class);

        app(QuickBillService::class)->createAndSettle(['999'], $client->id, 'cash', $staff->id);
    }

    public function test_create_and_settle_throws_for_empty_codes(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantContext::class)->set($tenant);
        $client = Client::factory()->create(['tenant_id' => $tenant->id]);
        $staff = User::factory()->for($tenant)->create();

        $this->expectException(InvalidArgumentException::class);

        app(QuickBillService::class)->createAndSettle([], $client->id, 'cash', $staff->id);
    }
}
