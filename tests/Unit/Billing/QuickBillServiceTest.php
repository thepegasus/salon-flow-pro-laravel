<?php

namespace Tests\Unit\Billing;

use App\Models\Bill;
use App\Models\Client;
use App\Models\Service;
use App\Models\StaffProfile;
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

    public function test_create_and_settle_builds_bill_from_resolved_items_and_marks_it_paid(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantContext::class)->set($tenant);
        $service = Service::factory()->create(['tenant_id' => $tenant->id, 'price' => 500]);
        $client = Client::factory()->create(['tenant_id' => $tenant->id]);
        $staff = User::factory()->for($tenant)->create();

        $bill = app(QuickBillService::class)->createAndSettle(
            [['service_id' => $service->id, 'quantity' => 1]],
            $client->id,
            'cash',
            $staff->id,
        );

        $this->assertSame(Bill::StatusPaid, $bill->status);
        $this->assertSame($client->id, $bill->client_id);
        $this->assertSame(1, $bill->lineItems()->count());
        $this->assertSame(1, $bill->payments()->where('method', 'cash')->count());
    }

    public function test_create_and_settle_uses_walk_in_client_when_no_client_given(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantContext::class)->set($tenant);
        $service = Service::factory()->create(['tenant_id' => $tenant->id]);
        $staff = User::factory()->for($tenant)->create();

        $bill = app(QuickBillService::class)->createAndSettle(
            [['service_id' => $service->id]],
            null,
            'cash',
            $staff->id,
        );

        $this->assertSame(QuickBillService::WalkInClientName, $bill->client->name);
    }

    public function test_create_and_settle_reuses_the_same_walk_in_client_across_bills(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantContext::class)->set($tenant);
        $service = Service::factory()->create(['tenant_id' => $tenant->id]);
        $staff = User::factory()->for($tenant)->create();

        $first = app(QuickBillService::class)->createAndSettle([['service_id' => $service->id]], null, 'cash', $staff->id);
        $second = app(QuickBillService::class)->createAndSettle([['service_id' => $service->id]], null, 'upi', $staff->id);

        $this->assertSame($first->client_id, $second->client_id);
    }

    public function test_create_and_settle_supports_quantity_greater_than_one(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantContext::class)->set($tenant);
        $service = Service::factory()->create(['tenant_id' => $tenant->id, 'price' => 200]);
        $client = Client::factory()->create(['tenant_id' => $tenant->id]);
        $staff = User::factory()->for($tenant)->create();

        $bill = app(QuickBillService::class)->createAndSettle(
            [['service_id' => $service->id, 'quantity' => 3]],
            $client->id,
            'cash',
            $staff->id,
        );

        $this->assertSame(3, $bill->lineItems->first()->quantity);
    }

    public function test_create_and_settle_supports_manual_items_without_a_service(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantContext::class)->set($tenant);
        $client = Client::factory()->create(['tenant_id' => $tenant->id]);
        $staff = User::factory()->for($tenant)->create();

        $bill = app(QuickBillService::class)->createAndSettle(
            [['description' => 'Retail shampoo', 'unit_price' => 350]],
            $client->id,
            'cash',
            $staff->id,
        );

        $this->assertSame('Retail shampoo', $bill->lineItems->first()->description);
        $this->assertNull($bill->lineItems->first()->service_id);
    }

    public function test_create_and_settle_throws_for_unknown_service(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantContext::class)->set($tenant);
        $client = Client::factory()->create(['tenant_id' => $tenant->id]);
        $staff = User::factory()->for($tenant)->create();

        $this->expectException(InvalidArgumentException::class);

        app(QuickBillService::class)->createAndSettle([['service_id' => 999999]], $client->id, 'cash', $staff->id);
    }

    public function test_create_and_settle_throws_for_empty_items(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantContext::class)->set($tenant);
        $client = Client::factory()->create(['tenant_id' => $tenant->id]);
        $staff = User::factory()->for($tenant)->create();

        $this->expectException(InvalidArgumentException::class);

        app(QuickBillService::class)->createAndSettle([], $client->id, 'cash', $staff->id);
    }

    public function test_create_and_settle_persists_eligible_staff_profile_on_line_item(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantContext::class)->set($tenant);
        $service = Service::factory()->create(['tenant_id' => $tenant->id]);
        $client = Client::factory()->create(['tenant_id' => $tenant->id]);
        $staffProfile = StaffProfile::factory()->create(['tenant_id' => $tenant->id]);
        $service->staff()->sync([$staffProfile->id]);
        $user = User::factory()->for($tenant)->create();

        $bill = app(QuickBillService::class)->createAndSettle(
            [['service_id' => $service->id, 'staff_profile_id' => $staffProfile->id]],
            $client->id,
            'cash',
            $user->id,
        );

        $this->assertSame($staffProfile->id, $bill->lineItems->first()->staff_profile_id);
    }

    public function test_create_and_settle_throws_when_staff_is_not_eligible_for_service(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantContext::class)->set($tenant);
        $service = Service::factory()->create(['tenant_id' => $tenant->id]);
        $client = Client::factory()->create(['tenant_id' => $tenant->id]);
        $ineligibleStaffProfile = StaffProfile::factory()->create(['tenant_id' => $tenant->id]);
        $user = User::factory()->for($tenant)->create();

        $this->expectException(InvalidArgumentException::class);

        app(QuickBillService::class)->createAndSettle(
            [['service_id' => $service->id, 'staff_profile_id' => $ineligibleStaffProfile->id]],
            $client->id,
            'cash',
            $user->id,
        );
    }
}
