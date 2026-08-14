<?php

namespace Tests\Regression;

use App\Models\MainDomain;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrossTenantSessionCannotAuthenticateFixTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Bug risk: once SESSION_DOMAIN shares one cookie across every tenant
     * subdomain and the main domain, a session created while logged into
     * one tenant is physically sent to every other tenant's subdomain too.
     * Without a check, if a stray user ID ever collided or the underlying
     * session/user resolution didn't re-scope per host, a session could
     * authenticate into a tenant the user doesn't belong to. This proves a
     * logged-in user's own session, when presented on a DIFFERENT tenant's
     * subdomain, is not honoured there — they are treated as logged out on
     * that subdomain, not granted access to it.
     */
    public function test_a_tenants_session_does_not_authenticate_on_a_different_tenant_subdomain(): void
    {
        MainDomain::factory()->create(['domain' => 'salonflow.test']);
        $tenantA = Tenant::factory()->create(['subdomain' => 'studio-a']);
        $tenantB = Tenant::factory()->create(['subdomain' => 'studio-b']);
        $user = User::factory()->for($tenantA)->create(['username' => 'owner', 'password' => bcrypt('secret123')]);

        $this->post('http://studio-a.salonflow.test/login', [
            'username' => 'owner',
            'password' => 'secret123',
        ]);

        $response = $this->get('http://studio-b.salonflow.test/dashboard');

        $response->assertRedirect('http://studio-b.salonflow.test/login');
    }
}
