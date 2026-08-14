<?php

namespace Tests\Regression;

use App\Models\MainDomain;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestOnMainDomainNoLongerRedirectLoopsFixTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Bug: redirectGuestsTo() in bootstrap/app.php unconditionally sent every
     * unauthenticated hit on an auth-protected route to "{host}/login" — but
     * the main domain (e.g. salonflow.test, no subdomain) has no /login
     * route at all. A request like GET salonflow.test/login matched the
     * {slug} tenant-dashboard wildcard route (slug="login" satisfies
     * [a-z0-9-]+), which requires auth; the guest got redirected back to
     * the exact same URL, looping forever (ERR_TOO_MANY_REDIRECTS). Fixed
     * by (1) making redirectGuestsTo redirect to the landing page instead of
     * /login when the request host is the main domain, and (2) excluding
     * reserved words (admin, login, register) from the {slug} route pattern
     * so they can never be mistaken for a tenant slug.
     */
    public function test_guest_hitting_login_shaped_path_on_main_domain_404s_instead_of_looping(): void
    {
        MainDomain::factory()->create(['domain' => config('tenancy.main_domain')]);

        $response = $this->get('http://'.config('tenancy.main_domain').'/login');

        $response->assertNotFound();
    }

    public function test_guest_hitting_a_real_tenant_slug_on_main_domain_redirects_to_landing_not_login(): void
    {
        MainDomain::factory()->create(['domain' => config('tenancy.main_domain')]);
        $tenant = Tenant::factory()->create(['slug' => 'mejora']);

        $response = $this->get('http://'.config('tenancy.main_domain').'/mejora');

        $response->assertRedirect('http://'.config('tenancy.main_domain').'/');
    }
}
