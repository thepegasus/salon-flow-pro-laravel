<?php

namespace Tests\Unit\Staff;

use App\Models\StaffProfile;
use App\Models\Tenant;
use App\Repositories\Contracts\StaffProfileRepositoryInterface;
use App\Services\StaffService;
use App\Services\TenantContext;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class StaffServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
    }

    public function test_create_persists_user_staff_profile_and_role(): void
    {
        $tenant = Tenant::factory()->create();

        $tenantContext = app(TenantContext::class);
        $tenantContext->set($tenant);

        $repository = Mockery::mock(StaffProfileRepositoryInterface::class);
        $repository->shouldReceive('create')
            ->once()
            ->andReturnUsing(fn (array $data) => StaffProfile::create($data));

        $service = new StaffService($repository, $tenantContext);

        $staffProfile = $service->create([
            'name' => 'Priya Nair',
            'username' => 'priya',
            'email' => 'priya@example.com',
            'password' => 'password123',
            'role' => 'Stylist',
            'job_title' => 'Senior Stylist',
        ]);

        $this->assertSame('Senior Stylist', $staffProfile->job_title);
        $this->assertSame($tenant->id, $staffProfile->user->tenant_id);
        $this->assertTrue($staffProfile->user->hasRole('Stylist'));
    }

    public function test_update_syncs_services_when_provided(): void
    {
        $tenant = Tenant::factory()->create();

        $tenantContext = app(TenantContext::class);
        $tenantContext->set($tenant);

        $staffProfile = StaffProfile::factory()->create(['tenant_id' => $tenant->id]);

        $repository = Mockery::mock(StaffProfileRepositoryInterface::class);
        $repository->shouldReceive('update')
            ->once()
            ->andReturnUsing(function ($profile, array $data) {
                $profile->update($data);

                return $profile;
            });

        $service = new StaffService($repository, $tenantContext);

        $service->update($staffProfile, ['job_title' => 'Manager', 'service_ids' => []]);

        $this->assertSame('Manager', $staffProfile->fresh()->job_title);
    }
}
