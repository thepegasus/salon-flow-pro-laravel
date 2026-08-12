<?php

namespace Database\Factories;

use App\Models\StaffProfile;
use App\Models\StaffShift;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StaffShift>
 */
class StaffShiftFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $staffProfile = StaffProfile::factory()->create();

        return [
            'tenant_id' => $staffProfile->tenant_id,
            'staff_profile_id' => $staffProfile->id,
            'day_of_week' => fake()->numberBetween(0, 6),
            'start_time' => '09:00',
            'end_time' => '18:00',
            'is_working' => true,
        ];
    }

    public function override(string $date, bool $isWorking = false): static
    {
        return $this->state(fn (array $attributes) => [
            'day_of_week' => null,
            'override_date' => $date,
            'is_working' => $isWorking,
        ]);
    }
}
