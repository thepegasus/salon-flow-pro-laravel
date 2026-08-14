<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ActsAsTenant;
use Tests\TestCase;

class TenantUrlTest extends TestCase
{
    use ActsAsTenant, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpTenant();
        $this->seed(PermissionSeeder::class);
    }

    /**
     * TenantUrl::route() is exercised end-to-end via a real request (rather
     * than calling it directly in isolation) because the {subdomain}/{slug}
     * route parameters it relies on are only populated by the real
     * middleware chain (EnsureTenantRequest sets URL::defaults()) during an
     * actual HTTP request lifecycle.
     */
    public function test_view_links_resolve_correctly_on_the_subdomain(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');
        $service = Service::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($owner)->getFromTenant('/services');

        $response->assertOk();
        $response->assertSee($this->tenantUrl("/services/{$service->id}"), false);
    }

    public function test_view_links_resolve_correctly_on_the_slug_path(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');
        $service = Service::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($owner)->get($this->bySlugUrl('/services'));

        $response->assertOk();
        $response->assertSee($this->bySlugUrl("/services/{$service->id}"), false);
    }
}
