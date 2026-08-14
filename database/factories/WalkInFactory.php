<?php

namespace Database\Factories;

use App\Models\Tenant;
use App\Models\WalkIn;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WalkIn>
 */
class WalkInFactory extends Factory
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
            'name' => fake()->name(),
            'phone' => fake()->numerify('9#########'),
            'status' => WalkIn::StatusWaiting,
            'joined_at' => now(),
        ];
    }
}
