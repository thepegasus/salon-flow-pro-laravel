<?php

namespace Database\Factories;

use App\Models\Bill;
use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Bill>
 */
class BillFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $client = Client::factory()->create();
        $user = User::factory()->for($client->tenant)->create();

        return [
            'tenant_id' => $client->tenant_id,
            'client_id' => $client->id,
            'bill_number' => fake()->unique()->numberBetween(1, 100000),
            'subtotal' => 500,
            'tax_amount' => 90,
            'total' => 590,
            'status' => Bill::StatusUnpaid,
            'created_by' => $user->id,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Bill::StatusPaid,
            'amount_paid' => $attributes['total'] ?? 590,
        ]);
    }
}
