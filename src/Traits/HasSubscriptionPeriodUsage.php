<?php


namespace Bpuig\Subby\Traits;


use Bpuig\Subby\Helpers\CarbonHelper;
use Illuminate\Support\Carbon;

trait HasSubscriptionPeriodUsage
{
    use HasSubscriptionPeriod;

    /**
     * Check if subscription is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return !$this->hasEndedGrace() || !$this->hasEnded() || $this->isOnTrial();
    }

    /**
     * Check if subscription is inactive.
     *
     * @return bool
     */
    public function isInactive(): bool
    {
        return !$this->isActive();
    }

    /**
     * Check if subscription is currently on trial.
     *
     * @return bool
     */
    public function isOnTrial(): bool
    {
        return $this->trial_ends_at && \Carbon\Carbon::now()->lt($this->trial_ends_at);
    }

    /**
     * Check if subscription is canceled.
     *
     * @return bool
     */
    public function isCanceled(): bool
    {
        return $this->canceled_at ? Carbon::now()->gte($this->canceled_at) : false;
    }

    /**
     * Check if subscription period has ended.
     *
     * @return bool
     */
    public function hasEnded(): bool
    {
        if (!$this->isOnTrial()) {
            return !$this->ends_at || Carbon::now()->gte($this->ends_at);
        }

        return false;
    }

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
