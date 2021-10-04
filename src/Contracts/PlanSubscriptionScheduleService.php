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
     * Logic for change of plan
     * @return mixed
     */
    public function changePlan();

    /**
     * Logic executed before plan change
     */
    public function execute();
}
