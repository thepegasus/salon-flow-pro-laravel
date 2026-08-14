<?php

namespace Tests\Unit\Services;

use App\Models\Service;
use App\Models\Tenant;
use App\Models\User;
use App\Repositories\Contracts\ServiceRepositoryInterface;
use App\Services\ServiceCatalogService;
use App\Services\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class ServiceCatalogServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_persists_service_and_records_initial_price_history(): void
    {
        $tenant = Tenant::factory()->create();
        $tenantContext = app(TenantContext::class);
        $tenantContext->set($tenant);
        $owner = User::factory()->for($tenant)->create();

        $repository = Mockery::mock(ServiceRepositoryInterface::class);
        $repository->shouldReceive('create')
            ->once()
            ->andReturnUsing(fn (array $data) => Service::create($data));

        $service = new ServiceCatalogService($repository, $tenantContext);

        $created = $service->create([
            'name' => 'Haircut',
            'price' => 499,
            'duration_minutes' => 45,
        ], changedBy: $owner->id);

        $this->assertSame('Haircut', $created->name);
        $this->assertSame(1, $created->priceHistories()->count());
    }

    public function test_update_records_new_price_history_only_when_price_changes(): void
    {
        $tenant = Tenant::factory()->create();
        $tenantContext = app(TenantContext::class);
        $tenantContext->set($tenant);
        $owner = User::factory()->for($tenant)->create();

        $existingService = Service::factory()->create(['tenant_id' => $tenant->id, 'price' => 499]);

        $repository = Mockery::mock(ServiceRepositoryInterface::class);
        $repository->shouldReceive('update')
            ->twice()
            ->andReturnUsing(function ($svc, array $data) {
                $svc->update($data);

                return $svc;
            });

        $service = new ServiceCatalogService($repository, $tenantContext);

        $service->update($existingService, ['name' => 'Renamed'], changedBy: $owner->id);
        $this->assertSame(0, $existingService->priceHistories()->count());

        $service->update($existingService, ['price' => 599], changedBy: $owner->id);
        $this->assertSame(1, $existingService->priceHistories()->count());
    }

    public function test_create_persists_the_pos_code(): void
    {
        $tenant = Tenant::factory()->create();
        $tenantContext = app(TenantContext::class);
        $tenantContext->set($tenant);
        $owner = User::factory()->for($tenant)->create();

        $repository = Mockery::mock(ServiceRepositoryInterface::class);
        $repository->shouldReceive('create')
            ->once()
            ->andReturnUsing(fn (array $data) => Service::create($data));

        $service = new ServiceCatalogService($repository, $tenantContext);

        $created = $service->create([
            'name' => 'Haircut',
            'code' => '101',
            'price' => 499,
            'duration_minutes' => 45,
        ], changedBy: $owner->id);

        $this->assertSame('101', $created->code);
    }

    public function test_update_persists_a_changed_pos_code(): void
    {
        $tenant = Tenant::factory()->create();
        $tenantContext = app(TenantContext::class);
        $tenantContext->set($tenant);
        $owner = User::factory()->for($tenant)->create();

        $existingService = Service::factory()->create(['tenant_id' => $tenant->id, 'code' => '101']);

        $repository = Mockery::mock(ServiceRepositoryInterface::class);
        $repository->shouldReceive('update')
            ->once()
            ->andReturnUsing(function ($svc, array $data) {
                $svc->update($data);

                return $svc;
            });

        $service = new ServiceCatalogService($repository, $tenantContext);
        $service->update($existingService, ['code' => '205'], changedBy: $owner->id);

        $this->assertSame('205', $existingService->fresh()->code);
    }

    public function test_deactivate_marks_service_inactive(): void
    {
        $tenant = Tenant::factory()->create();
        $tenantContext = app(TenantContext::class);
        $tenantContext->set($tenant);

        $existingService = Service::factory()->create(['tenant_id' => $tenant->id]);

        $repository = Mockery::mock(ServiceRepositoryInterface::class);
        $repository->shouldReceive('update')
            ->once()
            ->andReturnUsing(function ($svc, array $data) {
                $svc->update($data);

                return $svc;
            });

        $service = new ServiceCatalogService($repository, $tenantContext);
        $service->deactivate($existingService);

        $this->assertFalse($existingService->fresh()->is_active);
    }
}
