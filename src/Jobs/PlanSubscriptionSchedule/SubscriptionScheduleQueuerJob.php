<?php

namespace Bpuig\Subby\Jobs\PlanSubscriptionSchedule;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;

/**
 * Class SubscriptionScheduleQueuerJob
 * This Job creates a batch of jobs and dispatches them to queue
 * @package App\Jobs
 */
class SubscriptionScheduleQueuerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $planSubscriptionScheduleModel;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->planSubscriptionScheduleModel = app(config('subby.schedule.models.plan_subscription_schedule'));
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Throwable
     */
    public function handle()
    {
        // Get all queues of this job
        $queuedSchedules = $this->planSubscriptionScheduleModel
            ->pending()
            ->orderBy('scheduled_at', 'ASC')
            ->get();

        // Create a batch of the queued schedules
        $batch = [];
        foreach ($queuedSchedules as $queuedSchedule) {
            $batch[] = new SubscriptionScheduleProcessJob($queuedSchedule);
        }

        // Process the batch
        Bus::batch([$batch])
            ->allowFailures()
            ->dispatch();
    }
}
