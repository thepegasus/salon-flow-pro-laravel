<?php

namespace Tests\Integration\Appointments;

use App\Models\AppointmentReminder;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReminderDispatchCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_dispatches_due_reminders_across_all_active_tenants(): void
    {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();

        $reminderA = AppointmentReminder::factory()->due()->create(['tenant_id' => $tenantA->id]);
        $reminderB = AppointmentReminder::factory()->due()->create(['tenant_id' => $tenantB->id]);

        $this->artisan('reminders:send-due')->assertExitCode(0);

        $this->assertSame('sent', $reminderA->fresh()->status);
        $this->assertSame('sent', $reminderB->fresh()->status);
    }
}
