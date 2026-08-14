<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
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
            'category_id' => null,
            'name' => fake()->randomElement(['Keratin Shampoo 250ml', 'Hair Colour Tube', 'Nail Polish', 'Face Serum', 'Hair Spray']),
            'sku' => fake()->unique()->bothify('SKU-####'),
            'quantity_on_hand' => fake()->randomElement([5, 10, 25, 50, 100]),
            'reorder_level' => fake()->randomElement([5, 10, 15]),
            'unit' => fake()->randomElement(['ml', 'g', 'pcs']),
            'is_active' => true,
        ];
    }

    public function lowStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity_on_hand' => 2,
            'reorder_level' => 10,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
