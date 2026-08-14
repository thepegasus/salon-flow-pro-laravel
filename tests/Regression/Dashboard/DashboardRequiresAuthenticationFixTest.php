<?php

namespace Tests\Regression\Dashboard;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ActsAsTenant;
use Tests\TestCase;

class DashboardRequiresAuthenticationFixTest extends TestCase
{
    use ActsAsTenant, RefreshDatabase;

    /**
     * Bug: /dashboard was registered outside the 'auth' middleware group,
     * so a guest request reached TenantDashboardController and crashed on
     * auth()->user()->name once the dashboard stopped being a stub.
     */
    public function test_dashboard_no_longer_crashes_for_guests_and_redirects_to_login(): void
    {
        $this->setUpTenant();

        $response = $this->getFromTenant('/dashboard');

        $response->assertRedirect($this->tenantUrl('/login'));
    }
}
