<?php

namespace Bpuig\Subby\Jobs;

use Bpuig\Subby\Models\PlanSubscription;
use http\Exception\InvalidArgumentException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;

class SubscriptionRenewalPaymentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $planSubscription;
    private $service;

    public $tries = 1;
    public $timeout = 120;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($planSubscriptionId)
    {
        $this->planSubscription = PlanSubscription::find($planSubscriptionId);

        // Check if service exists in config file
        if (empty(config('subby.services.renewal'))) {
            throw new InvalidArgumentException('Selected Subscription Service does not exist', 401);
        }

        // Retrieve service from config
        $this->service = app()->make(config('subby.services.renewal'), ['planSubscription' => $this->planSubscription]);

        // Set options from service constants
        $this->tries = $this->service::TRIES;
        $this->timeout = $this->service::TIMEOUT;

    }

    // Avoid overlapping jobs to avoid any double payment issues
    public function middleware()
    {
        return [(new WithoutOverlapping('subscription-payment-' . $this->planSubscription->id))];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->service->execute();
    }
}
