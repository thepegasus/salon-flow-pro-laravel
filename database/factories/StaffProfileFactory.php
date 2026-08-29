<?php

namespace Database\Factories;

use App\Models\Designation;
use App\Models\StaffProfile;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StaffProfile>
 */
class StaffProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tenant = Tenant::factory();

        return [
            'tenant_id' => $tenant,
            'designation_id' => Designation::factory()->for($tenant),
            'phone' => fake()->phoneNumber(),
            'is_active' => true,
        ];
    }

    /** @return Factory<StaffProfile> */
    public function configure(): static
    {
        return $this->afterMaking(function (StaffProfile $staffProfile): void {
            if ($staffProfile->user_id !== null || $staffProfile->name !== null) {
                return;
            }

            $user = User::factory()->create(['tenant_id' => $staffProfile->tenant_id]);

            $staffProfile->user_id = $user->id;
            $staffProfile->name = $user->name;
            $staffProfile->email = $user->email;
        });
    }

    public function withoutLogin(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
            'name' => fake()->name(),
            'email' => null,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
