<?php


namespace Bpuig\Subby\Services\PlanSubscriptionSchedule;


use Bpuig\Subby\Contracts\PlanSubscriptionScheduleService;
use Bpuig\Subby\Traits\PlanSubscriptionSchedule\IsScheduleService;

class DefaultScheduleService implements PlanSubscriptionScheduleService
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
     *
     * Since this is kind of a dummy process, set success to true
     */
    public function execute()
    {
        $this->success = true;
        $this->clearUsage = true;
    }
}
