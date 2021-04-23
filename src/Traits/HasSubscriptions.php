<?php

declare(strict_types=1);

namespace Bpuig\Subby\Traits;

use Bpuig\Subby\Models\Plan;
use Bpuig\Subby\Models\PlanSubscription;
use Bpuig\Subby\Services\Period;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasSubscriptions
{
    /**
     * Define a polymorphic one-to-many relationship.
     *
     * @param string $related
     * @param string $name
     * @param string $type
     * @param string $id
     * @param string $localKey
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    abstract public function morphMany($related, $name, $type = null, $id = null, $localKey = null);

    /**
     * The subscriber may have many subscriptions.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function subscriptions(): MorphMany
    {
        return $this->morphMany(config('subby.models.plan_subscription'), 'subscriber', 'subscriber_type', 'subscriber_id');
    }

    /**
     * A model may have many active subscriptions.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function activeSubscriptions(): Collection
    {
        return $this->subscriptions->reject->inactive();
    }

    /**
     * Get a subscription by tag.
     *
     * @param string $subscriptionTag
     *
     * @return PlanSubscription|\Illuminate\Database\Eloquent\Model|MorphMany|null
     */
    public function subscription(string $subscriptionTag)
    {
        return $this->subscriptions()->where('tag', $subscriptionTag)->first();
    }

    /**
     * Get subscribed plans.
     *
     * @return \Bpuig\Subby\Models\PlanSubscription|null
     */
    public function subscribedPlans(): ?PlanSubscription
    {
        $planIds = $this->subscriptions->reject->inactive()->pluck('plan_id')->unique();

        return app(config('subby.models.plan'))->whereIn('id', $planIds)->get();
    }

    /**
     * Check if the subscriber is subscribed to the given plan.
     *
     * @param int $planId
     *
     * @return bool
     */
    public function subscribedTo(int $planId): bool
    {
        $subscription = $this->subscriptions()->where('plan_id', $planId)->first();

        return $subscription && $subscription->active();
    }

    /**
     * Subscribe subscriber to a new plan.
     *
     * @param string $tag Identifier tag for the subscription
     * @param \Bpuig\Subby\Models\Plan $plan
     * @param string $name Human readable name for your subscriber's subscription
     * @param \Carbon\Carbon|null $startDate
     *
     * @return \Illuminate\Database\Eloquent\Model
     * @throws \Exception
     */
    public function newSubscription(string $tag, Plan $plan, string $name, Carbon $startDate = null)
    {
        $trial = new Period($plan->trial_interval, $plan->trial_period, $startDate ?? now());
        $period = new Period($plan->invoice_interval, $plan->invoice_period, $trial->getEndDate());

        return $this->subscriptions()->create([
            'tag' => $tag,
            'name' => $name,
            'plan_id' => $plan->getKey(),
            'trial_ends_at' => $trial->getEndDate(),
            'starts_at' => $period->getStartDate(),
            'ends_at' => $period->getEndDate(),
        ]);
    }
}
