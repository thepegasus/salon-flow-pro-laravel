<?php

namespace Tests\Regression\Services;

use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ActsAsTenant;
use Tests\TestCase;

class GuestRedirectUsesRequestHostFixTest extends TestCase
{
    use ActsAsTenant, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpTenant();
        $this->seed(PermissionSeeder::class);
    }

    /**
     * Bug: the login route lives under a Route::domain('{subdomain}.'.$mainDomain)
     * group, so it requires a {subdomain} route parameter. Laravel's
     * ApplicationBuilder::withMiddleware() hardcodes a default
     * redirectGuestsTo(fn () => route('login')), which throws
     * UrlGenerationException without that parameter, turning every
     * unauthenticated request to a protected page into a 500 instead of a
     * redirect. Fixed by overriding redirectGuestsTo() in bootstrap/app.php
     * to build the login URL from the current request's own host instead
     * of the named route.
     */
    public function test_guest_visiting_a_protected_page_gets_redirected_not_a_500(): void
    {
        $response = $this->getFromTenant('/services');

        $response->assertRedirect($this->tenantUrl('/login'));
    }
}
