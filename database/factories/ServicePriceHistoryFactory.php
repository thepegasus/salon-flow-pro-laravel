<?php

namespace Database\Factories;

use App\Models\Service;
use App\Models\ServicePriceHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServicePriceHistory>
 */
class ServicePriceHistoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $service = Service::factory()->create();

        return [
            'tenant_id' => $service->tenant_id,
            'service_id' => $service->id,
            'price' => $service->price,
            'effective_from' => now(),
            'changed_by' => null,
        ];
    }
}
