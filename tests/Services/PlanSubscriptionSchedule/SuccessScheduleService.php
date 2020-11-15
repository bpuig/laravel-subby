<?php


namespace Bpuig\Subby\Tests\Services\PlanSubscriptionSchedule;


use Bpuig\Subby\Contracts\PlanSubscriptionScheduleService;
use Bpuig\Subby\Traits\PlanSubscriptionSchedule\IsScheduleService;

class SuccessScheduleService implements PlanSubscriptionScheduleService
{

    use IsScheduleService;

    /**
     * DefaultScheduleService constructor.
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
        $this->success = true;
    }
}
