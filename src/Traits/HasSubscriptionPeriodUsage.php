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
}
