<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\AppointmentReminder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AppointmentReminder>
 */
class AppointmentReminderFactory extends Factory
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
            'type' => AppointmentReminder::TypeReminder,
            'channel' => 'whatsapp',
            'scheduled_for' => now(),
            'status' => AppointmentReminder::StatusPending,
        ];
    }

    public function due(): static
    {
        return $this->state(fn (array $attributes) => [
            'scheduled_for' => now()->subMinutes(5),
        ]);
    }
}
