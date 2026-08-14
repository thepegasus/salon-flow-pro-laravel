<?php

namespace Tests\Regression;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ActsAsTenant;
use Tests\TestCase;

class AuthenticatedUserSkipsLoginFormFixTest extends TestCase
{
    use ActsAsTenant, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpTenant();
    }

    /**
     * Bug: LoginController::create() always rendered the login form, even
     * for an already-authenticated visitor (e.g. revisiting /login from
     * history, or arriving at salonflow.test/{slug}/login with a session
     * shared from the subdomain). Fixed to redirect straight to the
     * dashboard when already logged in.
     */
    public function test_authenticated_user_visiting_login_is_redirected_to_dashboard_on_subdomain(): void
    {
        $user = User::factory()->for($this->tenant)->create();

        $response = $this->actingAs($user)->getFromTenant('/login');

        $response->assertRedirect($this->tenantUrl('/dashboard'));
    }

    public function test_authenticated_user_visiting_login_is_redirected_to_dashboard_on_slug_path(): void
    {
        $user = User::factory()->for($this->tenant)->create();

        $response = $this->actingAs($user)->get($this->bySlugUrl('/login'));

        $response->assertRedirect($this->bySlugUrl('/dashboard'));
    }

    public function test_guest_still_sees_the_login_form(): void
    {
        $response = $this->getFromTenant('/login');

        $response->assertOk();
        $response->assertViewIs('auth.login');
    }
}
