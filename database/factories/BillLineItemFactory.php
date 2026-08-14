<?php

namespace Database\Factories;

use App\Models\Bill;
use App\Models\BillLineItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BillLineItem>
 */
class BillLineItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $bill = Bill::factory()->create();

        return [
            'tenant_id' => $bill->tenant_id,
            'bill_id' => $bill->id,
            'description' => fake()->words(2, true),
            'quantity' => 1,
            'unit_price' => 500,
            'tax_rate' => 18.00,
            'line_total' => 500,
        ];
    }
}
