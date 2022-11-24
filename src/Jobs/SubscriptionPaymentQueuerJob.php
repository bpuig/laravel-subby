<?php

namespace Bpuig\Subby\Jobs;

use Bpuig\Subby\Models\PlanSubscription;
use Bpuig\Subby\Models\PlanSubscriptionSchedule;
use Bpuig\Subby\Services\PendingPaymentCollector;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SubscriptionPaymentQueuerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $processUntil;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(?Carbon $processUntil = null)
    {
        $this->processUntil = ($processUntil) ?? Carbon::now();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $pendingPaymentCollector = new PendingPaymentCollector();
        $pendingPayments = $pendingPaymentCollector->onDate($this->processUntil)->collectAllPayments();

        foreach ($pendingPayments as $pendingPayment) {
            switch ($pendingPayment['collectable_type']) {
                case PlanSubscription::class:
                    SubscriptionRenewalPaymentJob::dispatch($pendingPayment['collectable_id']);
                    break;
                case PlanSubscriptionSchedule::class:
                    SubscriptionSchedulePaymentJob::dispatch($pendingPayment['collectable_id']);
                    break;
            }
        }
    }
}
