<?php

namespace Database\Factories;

use App\Models\CommissionRate;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CommissionRate>
 */
class CommissionRateFactory extends Factory
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
            'staff_profile_id' => null,
            'service_category_id' => null,
            'rate_percent' => fake()->randomElement([5, 10, 12.5, 15, 20]),
            'effective_from' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
        ];
    }

    public function forStaff(int $staffProfileId): static
    {
        return $this->state(fn (array $attributes) => [
            'staff_profile_id' => $staffProfileId,
        ]);
    }

    public function forCategory(int $serviceCategoryId): static
    {
        return $this->state(fn (array $attributes) => [
            'service_category_id' => $serviceCategoryId,
        ]);
    }
}
