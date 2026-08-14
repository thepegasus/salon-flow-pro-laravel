<?php

namespace Tests\Regression;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ActsAsTenant;
use Tests\TestCase;

class TenantRootNoLongerReturns404FixTest extends TestCase
{
    use ActsAsTenant, RefreshDatabase;

    /**
     * Bug: the {subdomain}.domain route group never registered a GET / route,
     * so visiting a tenant's bare subdomain (e.g. https://mejora.salonflow.test/)
     * returned a 404 instead of routing the visitor to the login screen or
     * their dashboard. Fixed by adding a root route that redirects based on
     * auth state.
     */
    public function test_tenant_root_no_longer_404s(): void
    {
        $this->setUpTenant();

        $response = $this->getFromTenant('/');

        $response->assertRedirect();
        $response->assertStatus(302);
    }
}
