<?php

namespace Database\Factories;

use App\Models\Bill;
use App\Models\BillPayment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BillPayment>
 */
class BillPaymentFactory extends Factory
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
            'method' => BillPayment::MethodCash,
            'amount' => 500,
        ];
    }
}
