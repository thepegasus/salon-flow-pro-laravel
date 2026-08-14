<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\StaffProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Appointment>
 */
class AppointmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $staffProfile = StaffProfile::factory()->create();
        $client = Client::factory()->create(['tenant_id' => $staffProfile->tenant_id]);
        $start = now()->addDay()->setTime(10, 0);

        return [
            'tenant_id' => $staffProfile->tenant_id,
            'client_id' => $client->id,
            'staff_profile_id' => $staffProfile->id,
            'start_at' => $start,
            'end_at' => $start->copy()->addMinutes(45),
            'status' => Appointment::StatusBooked,
        ];
    }

    public function noShow(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Appointment::StatusNoShow,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Appointment::StatusCancelled,
            'cancellation_reason' => 'client_requested',
        ]);
    }
}
