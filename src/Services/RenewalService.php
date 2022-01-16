<?php

declare(strict_types=1);

namespace Bpuig\Subby\Services;

use Bpuig\Subby\Contracts\PlanSubscriptionRenewalService;
use Bpuig\Subby\Models\PlanSubscription;
use function app;

class RenewalService implements PlanSubscriptionRenewalService
{
    private $planSubscription;

    /**
     * ScheduleService constructor.
     * @param PlanSubscription $planSubscription
     */
    public function __construct(PlanSubscription $planSubscription)
    {
        $this->planSubscription = $planSubscription;
    }

    /**
     * Execute the strategy
     * Try charging via default payment method and then renew subscription
     */
    public function execute()
    {
        try {
            $payment = app()->make(config('subby.services.payment_methods.' . $this->planSubscription->payment_method));
            $payment->charge();
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage(), $exception->getCode());
        }

        $this->planSubscription->renew();
    }
}
