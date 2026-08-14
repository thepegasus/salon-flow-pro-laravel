<?php

namespace Database\Factories;

use App\Models\BridalEngagement;
use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BridalEngagement>
 */
class BridalEngagementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $client = Client::factory()->create();

        return [
            'tenant_id' => $client->tenant_id,
            'client_id' => $client->id,
            'event_date' => now()->addMonth()->toDateString(),
            'venue' => fake()->address(),
            'status' => BridalEngagement::StatusPlanned,
        ];
    }
}
