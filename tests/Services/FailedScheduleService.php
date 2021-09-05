<?php


namespace Bpuig\Subby\Tests\Services;


use Bpuig\Subby\Contracts\PlanSubscriptionScheduleService;
use Bpuig\Subby\Traits\IsScheduleService;

class FailedScheduleService implements PlanSubscriptionScheduleService
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
     */
    public function execute()
    {
        $this->success = false;
    }
}
