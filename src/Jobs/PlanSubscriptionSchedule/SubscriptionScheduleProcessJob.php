<?php

namespace Bpuig\Subby\Jobs\PlanSubscriptionSchedule;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SubscriptionScheduleProcessJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 120;

    private $scheduleService;
    private $scheduleServiceConfig;
    private $planSubscriptionSchedule;

    /**
     * Create a new job instance.
     * @param $planSubscriptionSchedule
     * @throws \Exception
     */
    public function __construct($planSubscriptionSchedule)
    {
        $model = app(config('subby.schedule.models.plan_subscription_schedule'));

        if ($planSubscriptionSchedule instanceof $model === false) {
            throw new \Exception('Given data is not a Plan Subscription Schedule');
        }

        $this->planSubscriptionSchedule = $planSubscriptionSchedule;

        // Set options
        $this->tries = $this->planSubscriptionSchedule->tries;
        $this->timeout = $this->planSubscriptionSchedule->timeout;

        // Store config name
        $this->scheduleServiceConfig = 'subby.schedule.services.' . $this->planSubscriptionSchedule->service;

        // Check if service exists in config
        if (empty(config($this->scheduleServiceConfig))) {
            throw new \Exception('Selected Subscription Schedule Service does not exist', 401);
        }

        // Create instance of selected service
        $this->scheduleService = app()->make(config($this->scheduleServiceConfig), ['planSubscriptionSchedule' => $this->planSubscriptionSchedule]);
    }


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Execute the selected service process
        $this->scheduleService->execute();
        // Process change plan
        $this->scheduleService->changePlan();
    }
}
