<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Service;
use App\Models\ServiceProductUsage;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceProductUsage>
 */
class ServiceProductUsageFactory extends Factory
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
            'service_id' => Service::factory(),
            'product_id' => Product::factory(),
            'quantity_used' => fake()->randomElement([5, 10, 15, 20]),
        ];
    }
}
