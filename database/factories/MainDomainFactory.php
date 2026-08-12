<?php

namespace Database\Factories;

use App\Models\MainDomain;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MainDomain>
 */
class MainDomainFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'domain' => fake()->unique()->domainName(),
            'is_active' => true,
            'is_default' => false,
        ];
    }
}
