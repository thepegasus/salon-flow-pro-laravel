<?php

namespace Tests\Feature\Billing;

use App\Models\Client;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ActsAsTenant;
use Tests\TestCase;

class QuickBillAccessTest extends TestCase
{
    use ActsAsTenant, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpTenant();
        $this->seed(PermissionSeeder::class);
    }

    public function test_front_desk_can_open_quick_bill_screen(): void
    {
        $frontDesk = User::factory()->for($this->tenant)->create();
        $frontDesk->assignRole('FrontDesk');

        $response = $this->actingAs($frontDesk)->getFromTenant('/bills/quick');

        $response->assertOk();
    }

    public function test_stylist_cannot_open_quick_bill_screen(): void
    {
        $stylist = User::factory()->for($this->tenant)->create();
        $stylist->assignRole('Stylist');

        $response = $this->actingAs($stylist)->getFromTenant('/bills/quick');

        $response->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->getFromTenant('/bills/quick');

        $response->assertRedirect($this->tenantUrl('/login'));
    }

    public function test_lookup_service_returns_json_for_valid_code(): void
    {
        $user = User::factory()->for($this->tenant)->create();
        $user->assignRole('FrontDesk');
        Service::factory()->create(['tenant_id' => $this->tenant->id, 'code' => '101', 'name' => 'Haircut']);

        $response = $this->actingAs($user)->getFromTenant('/bills/quick/services/101');

        $response->assertOk()->assertJson(['found' => true, 'name' => 'Haircut']);
    }

    public function test_lookup_service_returns_404_json_for_unknown_code(): void
    {
        $user = User::factory()->for($this->tenant)->create();
        $user->assignRole('FrontDesk');

        $response = $this->actingAs($user)->getFromTenant('/bills/quick/services/999');

        $response->assertStatus(404)->assertJson(['found' => false]);
    }

    public function test_settle_creates_a_paid_bill_and_returns_redirect_url(): void
    {
        $user = User::factory()->for($this->tenant)->create();
        $user->assignRole('FrontDesk');
        Service::factory()->create(['tenant_id' => $this->tenant->id, 'code' => '101', 'price' => 500]);
        $client = Client::factory()->create(['tenant_id' => $this->tenant->id, 'phone' => '9000000000']);

        $response = $this->actingAs($user)->postToTenant('/bills/quick/settle', [
            'codes' => ['101'],
            'client_phone' => '9000000000',
            'payment_method' => 'cash',
        ]);

        $response->assertOk()->assertJsonStructure(['bill_id', 'bill_number', 'total', 'redirect']);
    }

    public function test_settle_rejects_unknown_client_phone(): void
    {
        $user = User::factory()->for($this->tenant)->create();
        $user->assignRole('FrontDesk');
        Service::factory()->create(['tenant_id' => $this->tenant->id, 'code' => '101']);

        $response = $this->actingAs($user)->postToTenant('/bills/quick/settle', [
            'codes' => ['101'],
            'client_phone' => '9999999999',
            'payment_method' => 'cash',
        ]);

        $response->assertStatus(422);
    }

    public function test_settle_validates_required_fields(): void
    {
        $user = User::factory()->for($this->tenant)->create();
        $user->assignRole('FrontDesk');

        $response = $this->actingAs($user)->postToTenant('/bills/quick/settle', [
            'codes' => [],
            'payment_method' => 'cash',
        ]);

        $response->assertSessionHasErrors('codes');
    }
}
