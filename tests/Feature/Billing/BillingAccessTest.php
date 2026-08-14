<?php

namespace Tests\Feature\Billing;

use App\Models\Bill;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ActsAsTenant;
use Tests\TestCase;

class BillingAccessTest extends TestCase
{
    use ActsAsTenant, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpTenant();
        $this->seed(PermissionSeeder::class);
    }

    public function test_front_desk_can_view_bills(): void
    {
        $frontDesk = User::factory()->for($this->tenant)->create();
        $frontDesk->assignRole('FrontDesk');

        $response = $this->actingAs($frontDesk)->getFromTenant('/bills');

        $response->assertOk();
    }

    public function test_front_desk_cannot_issue_a_refund(): void
    {
        $frontDesk = User::factory()->for($this->tenant)->create();
        $frontDesk->assignRole('FrontDesk');
        $bill = Bill::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($frontDesk)->putToTenant("/bills/{$bill->id}/refund", [
            'amount' => 100,
            'reason' => 'test',
        ]);

        $response->assertForbidden();
    }

    public function test_owner_can_issue_a_refund(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');
        $bill = Bill::factory()->create(['tenant_id' => $this->tenant->id, 'amount_paid' => 500]);

        $response = $this->actingAs($owner)->putToTenant("/bills/{$bill->id}/refund", [
            'amount' => 100,
            'reason' => 'Client complaint',
        ]);

        $response->assertRedirect();
    }

    public function test_stylist_cannot_view_bills(): void
    {
        $stylist = User::factory()->for($this->tenant)->create();
        $stylist->assignRole('Stylist');

        $response = $this->actingAs($stylist)->getFromTenant('/bills');

        $response->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->getFromTenant('/bills');

        $response->assertRedirect($this->tenantUrl('/login'));
    }
}
