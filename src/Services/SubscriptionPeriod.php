<?php


namespace Bpuig\Subby\Services;


use Bpuig\Subby\Models\Plan;
use Carbon\Carbon;

/**
 * Class SubscriptionPeriod
 *
 * Intermediate class to calculate subscription periods having plan trial in mind
 *
 * @package Bpuig\Subby\Services
 */
class SubscriptionPeriod
{
    protected $trial_end;
    protected $start = null;
    protected $end = null;

    protected $plan;
    protected $startDate;

    public function __construct(Plan $plan, Carbon $startDate)
    {
        $this->plan = $plan;
        $this->startDate = $startDate;

        $this->setTrialPeriod();
        $this->setSubscriptionPeriod();
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
     * Get trial end date.
     *
     * @return \Carbon\Carbon
     */
    public function getTrialEndDate(): Carbon
    {
        return $this->trial_end;
    }

    /**
     * Set trial period based on plan data
     */
    private function setTrialPeriod()
    {
        $trial = new Period($this->plan->trial_interval, $this->plan->trial_period, $this->startDate);
        $this->trial_end = $trial->getEndDate();
    }

    /**
     * Set subscription period
     */
    private function setSubscriptionPeriod()
    {
        if ($this->plan->trial_mode === 'detach') {
            // Detach has no subscription period
            return;
        }

        $startDate = $this->determineSubscriptionStartDate();

        $period = new Period($this->plan->invoice_interval, $this->plan->invoice_period, $startDate);
        $this->start = $period->getStartDate();
        $this->end = $period->getEndDate();
    }

    /**
     * Determine start date for period depending on trial mode
     * @return Carbon|void
     */
    private function determineSubscriptionStartDate()
    {
        if ($this->plan->trial_mode === 'prepend') return $this->trial_end;
        if ($this->plan->trial_mode === 'in_period') return $this->startDate;
    }
}
