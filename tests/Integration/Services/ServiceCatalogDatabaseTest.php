<?php

namespace Tests\Integration\Services;

use App\Models\Service;
use App\Models\Tenant;
use App\Services\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceCatalogDatabaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_persists_with_price_history_relationship(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantContext::class)->set($tenant);

        $service = Service::factory()->create(['tenant_id' => $tenant->id, 'price' => 499]);
        $service->priceHistories()->create([
            'tenant_id' => $tenant->id,
            'price' => 499,
            'effective_from' => now(),
        ]);

        $this->assertSame(1, $service->priceHistories()->count());
    }

    public function test_tenant_scope_excludes_services_from_other_tenants(): void
    {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();

        Service::factory()->create(['tenant_id' => $tenantA->id]);
        Service::factory()->create(['tenant_id' => $tenantB->id]);

        app(TenantContext::class)->set($tenantA);

        $this->assertSame(1, Service::count());
    }

    public function test_disabling_a_service_soft_deletes_neither_service_nor_its_price_history(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantContext::class)->set($tenant);

        $service = Service::factory()->create(['tenant_id' => $tenant->id]);
        $service->priceHistories()->create([
            'tenant_id' => $tenant->id,
            'price' => $service->price,
            'effective_from' => now(),
        ]);

        $service->update(['is_active' => false]);

        $this->assertDatabaseHas('services', ['id' => $service->id, 'is_active' => false]);
        $this->assertDatabaseHas('service_price_histories', ['service_id' => $service->id]);
    }
}
