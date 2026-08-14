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
        $tenant = Tenant::factory()->create();
        $user = User::factory()->for($tenant)->create();

        return [
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'designation_id' => Designation::factory()->for($tenant),
            'phone' => fake()->phoneNumber(),
            'is_active' => true,
        ];
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
