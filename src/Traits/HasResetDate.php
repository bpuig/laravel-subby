<?php


namespace Bpuig\Subby\Traits;


use Bpuig\Subby\Services\Period;
use Carbon\Carbon;

trait HasResetDate
{
    /**
     * Get feature's reset date.
     *
     * @param string $dateFrom
     *
     * @return \Carbon\Carbon
     * @throws \Exception
     */
    public function getResetDate(Carbon $dateFrom): Carbon
    {
        $period = new Period($this->resettable_interval, $this->resettable_period, $dateFrom ?? now());

        return $period->getEndDate();
    }
}
