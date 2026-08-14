<?php

namespace Tests\Regression\Billing;

use App\Models\Service;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ActsAsTenant;
use Tests\TestCase;

class QuickBillCodeLookupUsesRouteCodeNotSubdomainFixTest extends TestCase
{
    use ActsAsTenant, RefreshDatabase;

    /**
     * Bug: QuickBillController::lookupService(Request $request, string $code) had no
     * parameter for the {subdomain} route segment, so Laravel's route-model-binding
     * resolver shifted the subdomain string into the $code argument slot instead of
     * the actual code, making every code lookup 404. Fixed by declaring
     * string $subdomain before string $code, matching every other controller in
     * this app that sits on a {subdomain}.domain route with an extra parameter.
     */
    public function test_lookup_service_receives_the_actual_code_not_the_subdomain(): void
    {
        $this->setUpTenant();
        $this->seed(PermissionSeeder::class);

        $user = User::factory()->for($this->tenant)->create();
        $user->assignRole('FrontDesk');
        Service::factory()->create(['tenant_id' => $this->tenant->id, 'code' => '101', 'name' => 'Haircut']);

        $response = $this->actingAs($user)->getFromTenant('/bills/quick/services/101');

        $response->assertOk()->assertJson(['found' => true, 'name' => 'Haircut']);
    }
}
