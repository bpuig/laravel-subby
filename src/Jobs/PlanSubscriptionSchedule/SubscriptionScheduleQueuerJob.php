<?php

namespace Bpuig\Subby\Jobs\PlanSubscriptionSchedule;

use Carbon\Carbon;
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

    protected $until;

    /**
     * Create a new job instance.
     *
     * @param Carbon|null $until Limit date to be processed
     */
    public function __construct(Carbon $until = null)
    {
        $this->planSubscriptionScheduleModel = app(config('subby.schedule.models.plan_subscription_schedule'));

        $this->until = (is_null($until)) ? Carbon::now() : $until;
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
            ->pending($this->until)
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
