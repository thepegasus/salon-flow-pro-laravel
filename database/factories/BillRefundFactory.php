<?php

namespace Database\Factories;

use App\Models\Bill;
use App\Models\BillRefund;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BillRefund>
 */
class BillRefundFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $bill = Bill::factory()->create();
        $user = User::factory()->for($bill->client->tenant)->create();

        return [
            'tenant_id' => $bill->tenant_id,
            'bill_id' => $bill->id,
            'amount' => 100,
            'reason' => 'Service not satisfactory',
            'refunded_by' => $user->id,
        ];
    }
}
