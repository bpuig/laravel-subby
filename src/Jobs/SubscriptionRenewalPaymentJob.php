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
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function __construct($planSubscriptionId)
    {
        $this->planSubscription = PlanSubscription::find($planSubscriptionId);

        // Retrieve service name from config
        $paymentMethod = config('subby.services.payment_methods.' . $this->planSubscription->payment_method);

        // Check if service exists in config file
        if (empty($paymentMethod)) {
            throw new InvalidArgumentException('Selected payment method does not exist', 401);
        }

        // Make service
        $this->service = app()->make($paymentMethod);

        // Set options from service constants
        $this->tries = $this->service::TRIES;
        $this->timeout = $this->service::TIMEOUT;

    }

    // Avoid overlapping jobs to avoid any double payment issues
    public function middleware()
    {
        return [(new WithoutOverlapping('subscription-payment-' . $this->planSubscription->id))->dontRelease()];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->service
            ->subscription($this->planSubscription)
            ->amount($this->planSubscription->price)
            ->currency($this->planSubscription->currency)
            ->execute();
    }
}
