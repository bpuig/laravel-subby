<?php

declare(strict_types=1);

namespace Bpuig\Subby\Services;

use Bpuig\Subby\Models\PlanSubscription;
use Bpuig\Subby\Models\PlanSubscriptionSchedule;
use Carbon\Carbon;

class PendingPaymentCollector
{
    private $processUntilDate;

    public function __construct(?Carbon $date = null)
    {
        $this->processUntilDate = $date ?? now();
    }

    /**
     * Set date to do the collection
     * @param Carbon $date
     * @return $this
     */
    public function onDate(Carbon $date)
    {
        $this->processUntilDate = $date;

        return $this;
    }

    /**
     * Collect regular renewal payments into array
     * @return mixed
     */
    public function collectPayments()
    {
        return $this->getPayments()->sortBy('date', SORT_ASC)->all();
    }

    /**
     * Collect scheduled payments
     * @return mixed
     */
    public function collectScheduledPayments()
    {
        return $this->getScheduledPayments()->sortBy('date', SORT_ASC)->all();
    }

    /**
     * Collect all pending payments
     * This function collects regular renewals and scheduled, it prioritizes scheduled over regular renewals
     * @return mixed
     */
    public function collectAllPayments()
    {
        $regular = $this->getPayments();
        $scheduled = $this->getScheduledPayments();

        // Get only regular pending payments of subscriptions that are not already scheduled
        $regularDiff = $regular->whereNotIn('subscription_id', $scheduled->pluck('subscription_id'));

        // Sort by date and retrieve unique, to avoid messing up multiple pending subscription schedule changes
        return $scheduled->merge($regularDiff)->sortBy('date', SORT_ASC)->unique('subscription_id')->all();
    }

    /**
     * Get regular renewal payments
     * @return mixed
     */
    private function getPayments()
    {
        $pending = PlanSubscription::findPendingPayment($this->processUntilDate)->get();

        return $pending->map(function ($subscription) {
            return [
                'subscription_id' => $subscription->id,
                'collectable_type' => PlanSubscription::class,
                'collectable_id' => $subscription->id,
                'date' => $subscription->ends_at
            ];
        });
    }

    /**
     * Get scheduled payments
     * @return mixed
     */
    private function getScheduledPayments()
    {
        $pending = PlanSubscriptionSchedule::pending($this->processUntilDate)
            ->orderBy('scheduled_at', 'ASC')
            ->get();

        return $pending->map(function ($subscriptionSchedule) {
            return [
                'subscription_id' => $subscriptionSchedule->subscription_id,
                'collectable_type' => PlanSubscriptionSchedule::class,
                'collectable_id' => $subscriptionSchedule->id,
                'date' => $subscriptionSchedule->scheduled_at
            ];
        });
    }
}
