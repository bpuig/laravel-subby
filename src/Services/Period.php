<?php

declare(strict_types=1);

namespace Bpuig\Subby\Services;

use Bpuig\Subby\Helpers\CarbonHelper;
use Carbon\Carbon;

class Period
{
    /**
     * Starting date of the period.
     *
     * @var Carbon
     */
    protected $start;

    /**
     * Ending date of the period.
     *
     * @var Carbon
     */
    protected $end;

    /**
     * Interval.
     *
     * @var string
     */
    protected $interval;

    /**
     * Interval count.
     *
     * @var int
     */
    protected $period = 1;

    /**
     * Create a new Period instance.
     *
     * @param string $interval
     * @param int $count
     * @param string $start
     *
     * @return void
     * @throws \Exception
     */
    public function __construct($interval = 'month', $count = 1, $start = '')
    {
        $this->interval = $interval;

        if (empty($start)) {
            $this->start = Carbon::now();
        } elseif (!$start instanceof Carbon) {
            $this->start = new Carbon($start);
        } else {
            $this->start = $start;
        }

        $this->period = $count;

        $start = clone $this->start;
        $this->end = $start->{CarbonHelper::actionIn('add', $this->interval)}($this->period);
    }

    /**
     * Get start date.
     *
     * @return \Carbon\Carbon
     */
    public function getStartDate(): Carbon
    {
        return $this->start;
    }

    /**
     * Get end date.
     *
     * @return \Carbon\Carbon
     */
    public function getEndDate(): Carbon
    {
        return $this->end;
    }

    /**
     * Get period interval.
     *
     * @return string
     */
    public function getInterval(): string
    {
        return $this->interval;
    }

    /**
     * Get period interval count.
     *
     * @return int
     */
    public function getIntervalCount(): int
    {
        return $this->period;
    }
}
