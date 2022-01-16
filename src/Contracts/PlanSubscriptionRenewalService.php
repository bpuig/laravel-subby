<?php


namespace Bpuig\Subby\Contracts;


use Bpuig\Subby\Models\PlanSubscription;

interface PlanSubscriptionRenewalService
{
    const TRIES=3;
    const TIMEOUT=120;

    /**
     * PlanSubscriptionRenewalService constructor.
     * @param PlanSubscription $planSubscription
     */
    public function __construct(PlanSubscription $planSubscription);

    /**
     * Logic executed before subscription renewal
     */
    public function execute();
}
