<?php

namespace App\Services;

use App\Models\Bill;
use App\Models\Client;
use App\Models\Service;
use InvalidArgumentException;

class QuickBillService
{
    public const WalkInClientName = 'Walk-in customer';

    public function __construct(
        private BillingService $billingService,
        private TenantContext $tenantContext,
    ) {}

    public function findServiceByCode(string $code): ?Service
    {
        return Service::query()->active()->withCode($code)->first();
    }

    public function findClientByPhone(string $phone): ?Client
    {
        return Client::query()->where('phone', $phone)->first();
    }

    public function walkInClient(): Client
    {
        $tenant = $this->tenantContext->get();

        return Client::query()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => self::WalkInClientName, 'phone' => ''],
        );
    }

    /**
     * Creates a bill from scanned service codes and settles it in full with a single payment.
     *
     * @param  array<int, string>  $codes
     * @param  array<int, int|null>  $staffProfileIds  Eligible staff performing each code, keyed to match $codes
     */
    public function createAndSettle(array $codes, ?int $clientId, string $paymentMethod, int $staffUserId, array $staffProfileIds = []): Bill
    {
        if ($codes === []) {
            throw new InvalidArgumentException('At least one service code is required.');
        }

        $lineItems = [];
        foreach ($codes as $index => $code) {
            $service = $this->findServiceByCode($code);

            if (! $service) {
                throw new InvalidArgumentException("No active service found for code \"{$code}\".");
            }

            $staffProfileId = $staffProfileIds[$index] ?? null;

            if ($staffProfileId && ! $service->staff()->where('staff_profiles.id', $staffProfileId)->exists()) {
                throw new InvalidArgumentException("The selected staff member is not eligible to perform \"{$service->name}\".");
            }

            $lineItems[] = [
                'service_id' => $service->id,
                'staff_profile_id' => $staffProfileId,
                'description' => $service->name,
                'quantity' => 1,
                'unit_price' => (float) $service->price,
                'tax_rate' => 18.00,
            ];
        }

        $clientId ??= $this->walkInClient()->id;

        $bill = $this->billingService->createManualBill($clientId, $staffUserId, $lineItems);

        return $this->billingService->recordPayments($bill, [
            ['method' => $paymentMethod, 'amount' => (float) $bill->total],
        ], $staffUserId);
    }
}
