<?php

namespace App\Services;

use App\Models\Bill;
use App\Models\CommissionRate;
use App\Models\StaffIncentive;
use App\Models\StaffProfile;
use App\Repositories\Contracts\CommissionRateRepositoryInterface;
use App\Repositories\Contracts\StaffIncentiveRepositoryInterface;
use Illuminate\Support\Carbon;

class CommissionService
{
    public function __construct(
        private CommissionRateRepositoryInterface $commissionRateRepository,
        private StaffIncentiveRepositoryInterface $staffIncentiveRepository,
    ) {}

    /** @param array<string, mixed> $data */
    public function setRate(array $data): CommissionRate
    {
        return $this->commissionRateRepository->create($data);
    }

    /** @param array<string, mixed> $data */
    public function updateRate(CommissionRate $rate, array $data): CommissionRate
    {
        return $this->commissionRateRepository->update($rate, $data);
    }

    public function deleteRate(CommissionRate $rate): bool
    {
        return $this->commissionRateRepository->delete($rate);
    }

    /** @param array<string, mixed> $data */
    public function awardIncentive(array $data): StaffIncentive
    {
        return $this->staffIncentiveRepository->create($data);
    }

    /**
     * Resolves the applicable commission rate by specificity, most specific
     * tier first: exact staff + exact category, exact staff + all categories,
     * all staff + exact category, then the tenant-wide default (all staff +
     * all categories). Within a tier, the latest rate effective on or before
     * the given date wins.
     */
    public function resolveRateFor(StaffProfile $staff, ?int $serviceCategoryId, Carbon $onDate): string
    {
        $tiers = [
            ['staff_profile_id' => $staff->id, 'service_category_id' => $serviceCategoryId],
            ['staff_profile_id' => $staff->id, 'service_category_id' => null],
            ['staff_profile_id' => null, 'service_category_id' => $serviceCategoryId],
            ['staff_profile_id' => null, 'service_category_id' => null],
        ];

        foreach ($tiers as $tier) {
            $rate = CommissionRate::query()
                ->where('staff_profile_id', $tier['staff_profile_id'])
                ->where('service_category_id', $tier['service_category_id'])
                ->effectiveOn($onDate->toDateString())
                ->orderByDesc('effective_from')
                ->first();

            if ($rate) {
                return (string) $rate->rate_percent;
            }
        }

        return '0.00';
    }

    /** @return array{commissionEarned: string, incentivesEarned: string, totalEarned: string, lineItemCount: int} */
    public function earningsFor(StaffProfile $staff, Carbon $from, Carbon $to): array
    {
        $paidBills = Bill::query()
            ->where('status', Bill::StatusPaid)
            ->whereHas('appointment', function ($query) use ($staff): void {
                $query->where('staff_profile_id', $staff->id);
            })
            ->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->with(['lineItems.service'])
            ->get();

        $commissionEarned = '0.00';
        $lineItemCount = 0;

        foreach ($paidBills as $bill) {
            foreach ($bill->lineItems as $lineItem) {
                $categoryId = $lineItem->service?->category_id;
                $ratePercent = $this->resolveRateFor($staff, $categoryId, Carbon::parse($bill->created_at));

                $commission = bcmul((string) $lineItem->line_total, bcdiv($ratePercent, '100', 4), 2);
                $commissionEarned = bcadd($commissionEarned, $commission, 2);
                $lineItemCount++;
            }
        }

        $incentivesEarned = $this->staffIncentiveRepository
            ->getForStaffBetweenDates($staff, $from, $to)
            ->reduce(fn (string $carry, StaffIncentive $incentive) => bcadd($carry, (string) $incentive->amount, 2), '0.00');

        $totalEarned = bcadd($commissionEarned, $incentivesEarned, 2);

        return [
            'commissionEarned' => $commissionEarned,
            'incentivesEarned' => $incentivesEarned,
            'totalEarned' => $totalEarned,
            'lineItemCount' => $lineItemCount,
        ];
    }
}
