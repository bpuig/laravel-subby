<?php


namespace Bpuig\Subby\Services;


use Bpuig\Subby\Contracts\PlanSubscriptionScheduleService;
use Bpuig\Subby\Models\PlanSubscriptionSchedule;
use function app;

class ScheduleService implements PlanSubscriptionScheduleService
{
    private $planSubscriptionSchedule;

    /**
     * ScheduleService constructor.
     * Save current Plan Subscription Schedule
     * @param PlanSubscriptionSchedule $planSubscriptionSchedule
     */
    public function __construct(PlanSubscriptionSchedule $planSubscriptionSchedule)
    {
        $this->planSubscriptionSchedule = $planSubscriptionSchedule;
    }

    /**
     * Execute the strategy
     * Try charging via default payment method and then change plan
     * @throws \Exception
     */
    public function execute()
    {
        try {
            $payment = app()->make(config('subby.services.payment_methods.' . $this->planSubscriptionSchedule->subscription->payment_method));
            $payment->charge();
        } catch (\Exception $exception) {
            $this->planSubscriptionSchedule->fail();
            throw new \Exception($exception->getMessage(), $exception->getCode());
        }

        $this->planSubscriptionSchedule->changeSubscriptionPlan(true, true);
    }
}
