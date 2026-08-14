<?php

namespace Tests\Feature\Reports;

use App\Models\Bill;
use App\Models\BillLineItem;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ActsAsTenant;
use Tests\TestCase;

class ViewReportsTest extends TestCase
{
    use ActsAsTenant, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpTenant();
        $this->seed(PermissionSeeder::class);
    }

    public function test_owner_can_view_reports_on_the_subdomain(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');

        $response = $this->actingAs($owner)->getFromTenant('/reports');

        $response->assertOk();
        $response->assertViewIs('admin.reports.index');
    }

    public function test_owner_can_view_reports_on_the_slug_path(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');

        $response = $this->actingAs($owner)->get($this->bySlugUrl('/reports'));

        $response->assertOk();
        $response->assertViewIs('admin.reports.index');
    }

    public function test_stylist_cannot_view_reports(): void
    {
        $stylist = User::factory()->for($this->tenant)->create();
        $stylist->assignRole('Stylist');

        $response = $this->actingAs($stylist)->getFromTenant('/reports');

        $response->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->getFromTenant('/reports');

        $response->assertRedirect($this->tenantUrl('/login'));
    }

    public function test_reports_page_shows_revenue_for_the_selected_period(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');

        $bill = Bill::factory()->paid()->create([
            'tenant_id' => $this->tenant->id,
            'total' => 750,
        ]);
        BillLineItem::factory()->create([
            'tenant_id' => $this->tenant->id,
            'bill_id' => $bill->id,
            'description' => 'Bridal makeup',
            'line_total' => 750,
        ]);

        $response = $this->actingAs($owner)->getFromTenant('/reports?period=today');

        $response->assertOk();
        $response->assertSee('750.00', false);
        $response->assertSee('Bridal makeup');
    }
}
