<?php

namespace Bpuig\Subby\Jobs;

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
 * @package Bpuig\Subby\Jobs
 */
class SubscriptionScheduleQueuerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $planSubscriptionScheduleModel;

    protected $processUntil;

    /**
     * Create a new job instance.
     *
     * @param Carbon|null $processUntil Limit date to be processed
     */
    public function __construct(Carbon $processUntil = null)
    {
        $this->planSubscriptionScheduleModel = app(config('subby.models.plan_subscription_schedule'));

        $this->processUntil = ($processUntil) ?? Carbon::now();
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
            ->pending($this->processUntil)
            ->orderBy('scheduled_at', 'ASC')
            ->get();

        // Dispatch all pending jobs
        foreach ($queuedSchedules as $queuedSchedule) {
           SubscriptionScheduleProcessJob::dispatch($queuedSchedule);
        }
    }
}
