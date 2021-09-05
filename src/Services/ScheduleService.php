<?php


namespace Bpuig\Subby\Services;


use Bpuig\Subby\Contracts\PlanSubscriptionScheduleService;
use Bpuig\Subby\Traits\IsScheduleService;

class ScheduleService implements PlanSubscriptionScheduleService
{
    use IsScheduleService;

    /**
     * ScheduleService constructor.
     * Save current Plan Subscription Schedule
     * @param $planSubscriptionSchedule
     */
    public function __construct($planSubscriptionSchedule)
    {
        $this->planSubscriptionSchedule = $planSubscriptionSchedule;
    }

    /**
     * Execute the strategy
     *
     * Since this is kind of a dummy process, set success to true
     */
    public function execute()
    {
        $this->success = true;
    }
}
