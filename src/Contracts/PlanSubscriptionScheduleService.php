<?php


namespace Bpuig\Subby\Contracts;


interface PlanSubscriptionScheduleService
{
    /**
     * PlanSubscriptionScheduleService constructor.
     * @param $planSubscriptionSchedule
     */
    public function __construct($planSubscriptionSchedule);

    /**
     * Logic executed before plan change
     */
    public function execute();
}
