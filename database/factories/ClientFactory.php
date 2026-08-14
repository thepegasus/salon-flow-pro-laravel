<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
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
            'phone' => fake()->unique()->numerify('9#########'),
            'email' => fake()->optional()->safeEmail(),
            'is_frequent_no_show' => false,
        ];
    }

    public function frequentNoShow(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_frequent_no_show' => true,
        ]);
    }
}
