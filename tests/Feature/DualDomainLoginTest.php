<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ActsAsTenant;
use Tests\TestCase;

class DualDomainLoginTest extends TestCase
{
    use ActsAsTenant, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpTenant();
    }

    public function test_user_can_log_in_via_the_subdomain(): void
    {
        $user = User::factory()->for($this->tenant)->create(['username' => 'front.desk', 'password' => bcrypt('secret123')]);

        $response = $this->postToTenant('/login', ['username' => 'front.desk', 'password' => 'secret123']);

        $response->assertRedirect($this->tenantUrl('/dashboard'));
        $this->assertAuthenticatedAs($user->fresh());
    }

    public function test_user_can_log_in_via_the_main_domain_slug_path(): void
    {
        $user = User::factory()->for($this->tenant)->create(['username' => 'front.desk', 'password' => bcrypt('secret123')]);

        $response = $this->post($this->bySlugUrl('/login'), ['username' => 'front.desk', 'password' => 'secret123']);

        $response->assertRedirect($this->bySlugUrl('/dashboard'));
        $this->assertAuthenticatedAs($user->fresh());
    }

    public function test_logging_in_via_subdomain_is_recognised_on_the_slug_path_with_shared_session(): void
    {
        $user = User::factory()->for($this->tenant)->create(['username' => 'front.desk', 'password' => bcrypt('secret123')]);

        $this->postToTenant('/login', ['username' => 'front.desk', 'password' => 'secret123']);

        $response = $this->get($this->bySlugUrl('/dashboard'));

        $response->assertOk();
    }

    public function test_logging_in_via_slug_path_is_recognised_on_the_subdomain_with_shared_session(): void
    {
        $user = User::factory()->for($this->tenant)->create(['username' => 'front.desk', 'password' => bcrypt('secret123')]);

        $this->post($this->bySlugUrl('/login'), ['username' => 'front.desk', 'password' => 'secret123']);

        $response = $this->getFromTenant('/dashboard');

        $response->assertOk();
    }

    public function test_logging_out_via_slug_path_also_ends_the_subdomain_session(): void
    {
        $user = User::factory()->for($this->tenant)->create(['username' => 'front.desk', 'password' => bcrypt('secret123')]);
        $this->postToTenant('/login', ['username' => 'front.desk', 'password' => 'secret123']);

        $this->post($this->bySlugUrl('/logout'));

        $response = $this->getFromTenant('/dashboard');
        $response->assertRedirect($this->tenantUrl('/login'));
    }
}
