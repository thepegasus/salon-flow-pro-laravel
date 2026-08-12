<?php

namespace Tests\Feature\Staff;

use App\Models\StaffLeaveRequest;
use App\Models\StaffProfile;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ActsAsTenant;
use Tests\TestCase;

class LeaveRequestApprovalTest extends TestCase
{
    use ActsAsTenant, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpTenant();
        $this->seed(PermissionSeeder::class);
    }

    public function test_manager_can_approve_a_leave_request(): void
    {
        $manager = User::factory()->for($this->tenant)->create();
        $manager->assignRole('Manager');
        $staffProfile = StaffProfile::factory()->create(['tenant_id' => $this->tenant->id]);
        $leaveRequest = StaffLeaveRequest::factory()->create([
            'tenant_id' => $this->tenant->id,
            'staff_profile_id' => $staffProfile->id,
        ]);

        $response = $this->actingAs($manager)->putToTenant("/staff/leave-requests/{$leaveRequest->id}", [
            'status' => 'approved',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('staff_leave_requests', [
            'id' => $leaveRequest->id,
            'status' => 'approved',
            'decided_by' => $manager->id,
        ]);
    }

    public function test_manager_can_reject_a_leave_request(): void
    {
        $manager = User::factory()->for($this->tenant)->create();
        $manager->assignRole('Manager');
        $staffProfile = StaffProfile::factory()->create(['tenant_id' => $this->tenant->id]);
        $leaveRequest = StaffLeaveRequest::factory()->create([
            'tenant_id' => $this->tenant->id,
            'staff_profile_id' => $staffProfile->id,
        ]);

        $response = $this->actingAs($manager)->putToTenant("/staff/leave-requests/{$leaveRequest->id}", [
            'status' => 'rejected',
            'decision_note' => 'No coverage available',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('staff_leave_requests', [
            'id' => $leaveRequest->id,
            'status' => 'rejected',
            'decision_note' => 'No coverage available',
        ]);
    }

    public function test_stylist_cannot_decide_a_leave_request(): void
    {
        $stylist = User::factory()->for($this->tenant)->create();
        $stylist->assignRole('Stylist');
        $staffProfile = StaffProfile::factory()->create(['tenant_id' => $this->tenant->id]);
        $leaveRequest = StaffLeaveRequest::factory()->create([
            'tenant_id' => $this->tenant->id,
            'staff_profile_id' => $staffProfile->id,
        ]);

        $response = $this->actingAs($stylist)->putToTenant("/staff/leave-requests/{$leaveRequest->id}", [
            'status' => 'approved',
        ]);

        $response->assertForbidden();
    }
}
