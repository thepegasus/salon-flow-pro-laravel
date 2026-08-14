<?php

namespace Database\Factories;

use App\Models\StaffIncentive;
use App\Models\StaffProfile;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StaffIncentive>
 */
class StaffIncentiveFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'staff_profile_id' => StaffProfile::factory(),
            'amount' => fake()->randomElement([250, 500, 1000, 1500]),
            'reason' => fake()->randomElement(['Client praise', 'Top performer of the month', 'Referral bonus']),
            'awarded_date' => fake()->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
            'awarded_by' => User::factory(),
        ];
    }
}
