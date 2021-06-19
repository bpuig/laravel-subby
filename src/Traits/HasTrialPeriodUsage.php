<?php


namespace Bpuig\Subby\Traits;


use Bpuig\Subby\Helpers\CarbonHelper;
use Bpuig\Subby\Services\Period;
use Illuminate\Support\Carbon;

trait HasTrialPeriodUsage
{
    /**
     * Trial total duration in specified interval
     * @param string $interval
     * @return int
     * @throws \Exception
     */
    public function getTrialTotalDurationIn(string $interval) :int
    {
        $trialPeriod = new Period($this->plan->trial_interval, $this->plan->trial_period);
        return $trialPeriod->getStartDate()->{CarbonHelper::diffIn($interval)}($trialPeriod->getEndDate());
    }

    /**
     * Trial start date function
     * @return mixed
     */
    public function getTrialStartDate()
    {
        return $this->created_at;
    }

    /**
     * Trial period usage
     * @param string $interval
     * @return int
     * @throws \Exception
     */
    public function getTrialPeriodUsageIn(string $interval) :int
    {
        $diff = $this->getTrialStartDate()->{CarbonHelper::diffIn($interval)}(Carbon::now());

        return ($diff > $this->getTrialTotalDurationIn($interval)) ? $this->getTrialTotalDurationIn($interval) : $diff;
    }

    /**
     * Remaining trial period usage
     * @param string $interval
     * @return int
     */
    public function getTrialPeriodRemainingUsageIn(string $interval): int
    {
        return Carbon::now()->{CarbonHelper::diffIn($interval)}($this->trial_ends_at);
    }


}
