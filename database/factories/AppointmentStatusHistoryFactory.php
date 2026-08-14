<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\AppointmentStatusHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AppointmentStatusHistory>
 */
class AppointmentStatusHistoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $appointment = Appointment::factory()->create();

        return [
            'tenant_id' => $appointment->tenant_id,
            'appointment_id' => $appointment->id,
            'from_status' => 'booked',
            'to_status' => 'cancelled',
            'reason' => 'client_requested',
        ];
    }
}
