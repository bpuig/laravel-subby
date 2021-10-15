<?php


namespace Bpuig\Subby\Traits;


use Bpuig\Subby\Helpers\CarbonHelper;
use Illuminate\Support\Carbon;

trait HasGracePeriodUsage
{
    use HasGracePeriod;

    /**
     * Grace start date function
     * @return mixed
     */
    public function getGraceStartDate()
    {
        return $this->ends_at;
    }

    /**
     * Grace end date function
     * @return mixed
     */
    public function getGraceEndDate()
    {
        return $this->getGraceStartDate()->add($this->grace_period, $this->grace_interval);
    }

    /**
     * Grace period usage
     * @param string $interval
     * @return int
     * @throws \Exception
     */
    public function getGracePeriodUsageIn(string $interval): int
    {
        if (!$this->getGraceStartDate()) {
            return 0;
        }

        $diff = $this->getGraceStartDate()->{CarbonHelper::diffIn($interval)}(Carbon::now());

        return ($diff > $this->getGraceTotalDurationIn($interval)) ? $this->getGraceTotalDurationIn($interval) : $diff;
    }

    /**
     * Remaining trial period usage
     * @param string $interval
     * @return int
     */
    public function getGracePeriodRemainingUsageIn(string $interval): int
    {
        if (!$this->getGraceStartDate()) {
            return 0;
        }

        return Carbon::now()->{CarbonHelper::diffIn($interval)}($this->getGraceEndDate());
    }

    /**
     * Check if entity has started grace
     *
     * @return bool
     */
    public function hasStartedGrace(): bool
    {
        return $this->getGraceStartDate() && \Carbon\Carbon::now()->gt($this->getGraceStartDate());
    }

    /**
     * Check if entity has ended grace
     *
     * @return bool
     */
    public function hasEndedGrace(): bool
    {
        return !$this->getGraceStartDate() || \Carbon\Carbon::now()->gt($this->getGraceEndDate());
    }

    /**
     * Check if entity is in grace period
     * @return bool
     */
    public function isInGrace(): bool
    {
        return $this->hasStartedGrace() && !$this->hasEndedGrace();
    }
}
