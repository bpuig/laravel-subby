<?php


namespace Bpuig\Subby\Traits;


use Bpuig\Subby\Helpers\CarbonHelper;
use Illuminate\Support\Carbon;

trait HasSubscriptionPeriodUsage
{
    use HasSubscriptionPeriod;

    /**
     * Subscription period used
     * @param string $interval
     * @return int
     * @throws \Exception
     */
    public function getSubscriptionPeriodUsageIn(string $interval): int
    {
        return $this->starts_at->{CarbonHelper::diffIn($interval)}(Carbon::now());
    }

    /**
     * Remaining subscription period duration
     * @param string $interval
     * @return int
     */
    public function getSubscriptionPeriodRemainingUsageIn(string $interval): int
    {
        return Carbon::now()->{CarbonHelper::diffIn($interval)}($this->ends_at);
    }


    /**
     * Get the proportion of the remaining billing period
     * @return float
     * @throws \Exception
     */
    public function getRemainingSubscriptionPeriodProportion(): float
    {
        return round($this->getSubscriptionPeriodRemainingUsageIn('second') / $this->getSubscriptionTotalDurationIn('second'), 4);
    }

    /**
     * Get prorated price of subscription value
     * @return float
     * @throws \Exception
     */
    public function getSubscriptionRemainingUsagePriceProrate(): float
    {
        return round($this->price * $this->getRemainingSubscriptionPeriodProportion(), 2);
    }
}
