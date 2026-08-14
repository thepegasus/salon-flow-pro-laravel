<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ActsAsTenant;
use Tests\TestCase;

class TenantRootRedirectTest extends TestCase
{
    use ActsAsTenant, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpTenant();
    }

    public function test_guest_hitting_tenant_root_is_redirected_to_login(): void
    {
        $response = $this->getFromTenant('/');

        $response->assertRedirect($this->tenantUrl('/login'));
    }

    public function test_authenticated_user_hitting_tenant_root_is_redirected_to_dashboard(): void
    {
        $user = User::factory()->for($this->tenant)->create();

        $response = $this->actingAs($user)->getFromTenant('/');

        $response->assertRedirect($this->tenantUrl('/dashboard'));
    }

    public function test_authenticated_user_can_reach_dashboard_via_the_main_domain_slug_path(): void
    {
        $user = User::factory()->for($this->tenant)->create();

        $response = $this->actingAs($user)->get('http://salonflow.test/'.$this->tenant->slug.'/dashboard');

        $response->assertOk();
    }
}
