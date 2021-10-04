<?php

namespace Bpuig\Subby\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use InvalidArgumentException;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class SubscriptionScheduleProcessJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
        $model = app(config('subby.models.plan_subscription_schedule'));

        // Check if passed schedule is an instance of the model specified in config file
        if ($planSubscriptionSchedule instanceof $model === false) {
            throw new InvalidArgumentException('Given data is not a Plan Subscription Schedule');
        }

        $this->planSubscriptionSchedule = $planSubscriptionSchedule;

        // Set options
        $this->tries = $this->planSubscriptionSchedule->tries;
        $this->timeout = $this->planSubscriptionSchedule->timeout;
        $this->scheduleServiceConfig = 'subby.services.schedule.' . $this->planSubscriptionSchedule->service;

        // Check if service exists in config file
        if (empty(config($this->scheduleServiceConfig))) {
            throw new InvalidArgumentException('Selected Subscription Schedule Service does not exist', 401);
        }

        // Create instance of selected service and inject plan's subscription schedule
        $this->scheduleService = app()->make(config($this->scheduleServiceConfig), ['planSubscriptionSchedule' => $this->planSubscriptionSchedule]);
    }

    // Avoid overlapping jobs, so it is not paid double, etc.
    // Release job for retry after 60 seconds
    public function middleware()
    {
        return [(new WithoutOverlapping($this->planSubscriptionSchedule->id))->releaseAfter(60)];
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
