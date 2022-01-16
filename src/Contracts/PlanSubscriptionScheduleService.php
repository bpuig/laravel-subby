<?php


namespace Bpuig\Subby\Contracts;


use Bpuig\Subby\Models\PlanSubscriptionSchedule;

interface PlanSubscriptionScheduleService
{
    const TRIES=3;
    const TIMEOUT=120;

    /**
     * PlanSubscriptionScheduleService constructor.
     * @param PlanSubscriptionSchedule $planSubscriptionSchedule
     */
    public function __construct(PlanSubscriptionSchedule $planSubscriptionSchedule);

    /**
     * Logic executed before plan change
     */
    public function execute();
}
