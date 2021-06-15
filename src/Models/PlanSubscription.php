<?php

declare(strict_types=1);

namespace Bpuig\Subby\Models;

use BadMethodCallException;
use Bpuig\Subby\Services\Period;
use Bpuig\Subby\Traits\BelongsToPlan;
use Bpuig\Subby\Traits\HasFeatures;
use Bpuig\Subby\Traits\HasPricing;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Validation\Rule;
use LogicException;

class PlanSubscription extends Model
{
    use BelongsToPlan, HasFeatures, HasPricing;

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'tag',
        'subscriber_id',
        'subscriber_type',
        'plan_id',
        'name',
        'description',
        'price',
        'currency',
        'invoice_period',
        'invoice_interval',
        'tier',
        'trial_ends_at',
        'starts_at',
        'ends_at',
        'cancels_at',
        'canceled_at',
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'tag' => 'string',
        'subscriber_type' => 'string',
        'price' => 'float',
        'currency' => 'string',
        'invoice_period' => 'integer',
        'invoice_interval' => 'string',
        'tier' => 'integer',
        'trial_ends_at' => 'datetime',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'cancels_at' => 'datetime',
        'canceled_at' => 'datetime'
    ];

    /**
     * Create a new Eloquent model instance.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('subby.tables.plan_subscriptions'));
    }

    /**
     * Get validation rules
     * @return string[]
     */
    public function getRules(): array
    {
        return [
            'tag' => [
                'required',
                'alpha_dash',
                'max:150',
                Rule::unique(config('subby.tables.plan_subscriptions'))->where(function ($query) {
                    return $query->where('id', '!=', $this->id)->where('subscriber_type', $this->subscriber_type)
                        ->where('subscriber_id', $this->subscriber_id);
                }),
            ],
            'subscriber_id' => 'required|integer',
            'subscriber_type' => 'required|string|max:150',
            'plan_id' => 'required|integer|exists:' . config('subby.tables.plans') . ',id',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string|max:32768',
            'price' => 'required|numeric',
            'currency' => 'required|alpha|size:3',
            'invoice_period' => 'sometimes|integer|max:100000',
            'invoice_interval' => 'sometimes|in:hour,day,week,month',
            'tier' => 'nullable|integer|max:100000',
            'trial_ends_at' => 'nullable|date',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date',
            'cancels_at' => 'nullable|date',
            'canceled_at' => 'nullable|date',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $model) {
            if (!$model->starts_at || !$model->ends_at) {
                $model->setNewPeriod();
            }
        });
    }

    /**
     * Get the owning subscriber.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function subscriber(): MorphTo
    {
        return $this->morphTo('subscriber', 'subscriber_type', 'subscriber_id', 'id');
    }

    /**
     * Get subscription features
     * @return HasMany
     */
    public function features(): HasMany
    {
        return $this->hasMany(config('subby.models.plan_subscription_feature'), 'plan_subscription_id', 'id');
    }

    /**
     * The subscription may have many usage.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function usage(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(
            config('subby.models.plan_subscription_usage'),
            config('subby.models.plan_subscription_feature'),
            'plan_subscription_id',
            'plan_subscription_feature_id',
            'id',
            'id'
        );
    }

    /**
     * Check if subscription is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return !$this->hasEnded() || $this->isOnTrial();
    }

    /**
     * Check if subscription is inactive.
     *
     * @return bool
     */
    public function isInactive(): bool
    {
        return !$this->isActive();
    }

    /**
     * Check if subscription is currently on trial.
     *
     * @return bool
     */
    public function isOnTrial(): bool
    {
        return $this->trial_ends_at ? Carbon::now()->lt($this->trial_ends_at) : false;
    }

    /**
     * Check if subscription is canceled.
     *
     * @return bool
     */
    public function isCanceled(): bool
    {
        return $this->canceled_at ? Carbon::now()->gte($this->canceled_at) : false;
    }

    /**
     * Check if subscription period has ended.
     *
     * @return bool
     */
    public function hasEnded(): bool
    {
        return $this->ends_at ? Carbon::now()->gte($this->ends_at) : false;
    }

    /**
     * Check if subscription features have been altered
     * @return bool
     */
    public function isAltered(): bool
    {
        $planFeatures = collect($this->plan->features()->select('tag', 'value', 'resettable_period', 'resettable_interval')->get());
        $currentFeatures = collect($this->features()->select('tag', 'value', 'resettable_period', 'resettable_interval')->get());

        return $currentFeatures->diff($planFeatures)->count() > 0;
    }

    /**
     * Cancel subscription.
     *
     * @param bool $immediately
     *
     * @return $this
     */
    public function cancel(bool $immediately = false): PlanSubscription
    {
        $this->canceled_at = Carbon::now();

        // If cancel is immediate, set end date
        if ($immediately) {
            $this->cancels_at = $this->canceled_at;
            $this->ends_at = $this->canceled_at;
        } else {
            // If cancel is not immediate, it will be cancelled at period end
            $this->cancels_at = $this->ends_at;
        }

        $this->save();

        return $this;
    }

    /**
     * Uncancel subscription
     *
     * This action undoes all cancel flags
     *
     * @return $this
     */
    public function uncancel(): PlanSubscription
    {
        $this->canceled_at = null;
        $this->cancels_at = null;

        $this->save();

        return $this;
    }

    /**
     * Change subscription plan.
     *
     * @param \Bpuig\Subby\Models\Plan $plan
     * @param bool $clearUsage Clear subscription usage
     * @param bool $syncInvoicing Synchronize billing frequency or leave it unchanged
     * @return $this
     * @throws \Exception
     */
    public function changePlan(Plan $plan, bool $clearUsage = true, bool $syncInvoicing = true): PlanSubscription
    {
        // Sometimes you want to keep usage
        // E.g. of false: Renew plan at day 6 of subscription,
        // and if you consumed 2 resources, you keep having 2 consumed of
        // the new limit
        if ($clearUsage) {
            $this->usage()->delete();
        }

        // Synchronize subscription data with plan
        $this->syncPlan($plan, $syncInvoicing, true);

        return $this;
    }

    /**
     * Synchronize subscription data with plan
     * @param Plan|null $plan Plan to be synchronized
     * @param bool $syncInvoicing Synchronize billing frequency or leave it unchanged
     * @param bool $syncFeatures
     * @return PlanSubscription
     * @throws \Exception
     */
    public function syncPlan(Plan $plan = null, bool $syncInvoicing = true, bool $syncFeatures = false): PlanSubscription
    {
        if (!$plan && !$this->plan) {
            throw new BadMethodCallException('Default plan not set.');
        } elseif (!$plan) {
            $plan = $this->plan;
        }

        $this->plan_id = $plan->id;
        $this->price = $plan->price;
        $this->currency = $plan->currency;
        $this->tier = $plan->tier;

        if ($syncInvoicing) {
            // Set new start and end date
            $this->setNewPeriod($plan->invoice_interval, $plan->invoice_period);

            // Set same invoicing as selected plan
            $this->invoice_interval = $plan->invoice_interval;
            $this->invoice_period = $plan->invoice_period;
        }

        $this->save();

        if ($syncFeatures) {
            $this->syncPlanFeatures($plan);
        }

        return $this;
    }

    /**
     * Synchronize features with current plan
     * @param Plan|null $plan
     */
    public function syncPlanFeatures(Plan $plan = null): PlanSubscription
    {
        if (!$plan && !$this->plan) {
            throw new BadMethodCallException('Default plan not set.');
        } elseif (!$plan) {
            $plan = $this->plan;
        }

        DB::transaction(function () use ($plan) {
            $this->deleteFeaturesNotInPlan($plan);
            $this->updatePlanFeatures($plan);
        });

        return $this;
    }

    /**
     * Remove features that have plan related but are no longer in selected plan
     * @param Plan $plan Plan to be compared
     */
    private function deleteFeaturesNotInPlan(Plan $plan)
    {
        // Retrieve current features that are not related to a plan
        $featuresWithPlan = $this->features()->withoutPlan()->get();

        // Retrieve selected plan features
        $planFeatures = $plan->features();

        // Use tags to find which features are no longer in selected plan
        $featuresWithPlanTags = $featuresWithPlan->pluck('tag');
        $planFeatureTags = $planFeatures->pluck('tag');

        $featuresWithoutPlan = $featuresWithPlanTags->diff($planFeatureTags);

        // Delete not found features
        $this->features()->whereIn('tag', $featuresWithoutPlan->all())->delete();
    }

    /**
     * Update subscription features to have same features as selected plan
     * @param Plan $plan Plan to be compared
     */
    private function updatePlanFeatures(Plan $plan)
    {
        // Update selected plan features
        // if they do not exist, will be created
        // if they exist but are update to another feature_id or detached from feature, will be attached to plan feature
        foreach ($plan->features as $planFeature) {
            $this->features()->updateOrCreate(
                ['tag' => $planFeature->tag],
                [
                    'plan_feature_id' => $planFeature->id,
                    'name' => $planFeature->name,
                    'description' => $planFeature->description,
                    'value' => $planFeature->value,
                    'resettable_period' => $planFeature->resettable_period,
                    'resettable_interval' => $planFeature->resettable_interval,
                    'sort_order' => $planFeature->sort_order,
                ]);
        }
    }

    /**
     * Renew subscription period.
     *
     * @return $this
     * @throws \LogicException
     *
     */
    public function renew(): PlanSubscription
    {
        if ($this->isCanceled()) {
            throw new LogicException('Unable to renew canceled subscription.');
        }

        $subscription = $this;

        DB::transaction(function () use ($subscription) {
            // Clear usage data
            $subscription->usage()->delete();

            // End trial
            if ($subscription->isOnTrial()) {
                $subscription->trial_ends_at = Carbon::now();
            }

            // Renew period
            $subscription->setNewPeriod();
            $subscription->save();
        });

        return $this;
    }

    /**
     * Get bookings of the given subscriber.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param \Illuminate\Database\Eloquent\Model $subscriber
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfSubscriber(Builder $builder, Model $subscriber): Builder
    {
        return $builder->where('subscriber_type', $subscriber->getMorphClass())->where('subscriber_id', $subscriber->getKey());
    }

    /**
     * Scope subscriptions with ending trial.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param int $dayRange
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFindEndingTrial(Builder $builder, int $dayRange = 3): Builder
    {
        $from = Carbon::now();
        $to = Carbon::now()->addDays($dayRange);

        return $builder->whereBetween('trial_ends_at', [$from, $to]);
    }

    /**
     * Scope subscriptions with ended trial.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFindEndedTrial(Builder $builder): Builder
    {
        return $builder->where('trial_ends_at', '<=', now());
    }

    /**
     * Scope subscriptions with ending periods.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param int $dayRange
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFindEndingPeriod(Builder $builder, int $dayRange = 3): Builder
    {
        $from = Carbon::now();
        $to = Carbon::now()->addDays($dayRange);

        return $builder->whereBetween('ends_at', [$from, $to]);
    }

    /**
     * Scope subscriptions with ended periods.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFindEndedPeriod(Builder $builder): Builder
    {
        return $builder->where('ends_at', '<=', now());
    }

    /**
     * Scope subscriptions by tag.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param string $tag
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeGetByTag(Builder $builder, string $tag): Builder
    {
        return $builder->where('tag', $tag);
    }

    /**
     * Set new subscription period.
     *
     * @param string $invoice_interval
     * @param string $invoice_period
     * @param string $start
     *
     * @return $this
     * @throws \Exception
     */
    protected function setNewPeriod($invoice_interval = '', $invoice_period = '', $start = ''): PlanSubscription
    {
        if (empty($invoice_interval)) {
            $invoice_interval = $this->invoice_interval;
        }

        if (empty($invoice_period)) {
            $invoice_period = $this->invoice_period;
        }

        $period = new Period($invoice_interval, $invoice_period, $start);

        $this->starts_at = $period->getStartDate();
        $this->ends_at = $period->getEndDate();

        return $this;
    }

    /**
     * Record feature usage.
     *
     * @param string $featureTag
     * @param int $uses
     *
     * @param bool $incremental
     * @return PlanSubscriptionUsage|Model
     */
    public function recordFeatureUsage(string $featureTag, int $uses = 1, bool $incremental = true)
    {
        $feature = $this->getFeatureByTag($featureTag);


        $usage = $this->usage()->firstOrNew([
            'plan_subscription_feature_id' => $feature->getKey()
        ]);


        if ($feature->resettable_period) {
            // Set expiration date when the usage record is new or doesn't have one.
            if (is_null($usage->valid_until)) {
                // Set date from subscription creation date so the reset
                // period match the period specified by the subscription's plan.
                $usage->valid_until = $feature->getResetDate($this->created_at);
            } elseif ($usage->hasExpired()) {
                // If the usage record has been expired, let's assign
                // a new expiration date and reset the uses to zero.
                $usage->valid_until = $feature->getResetDate($usage->valid_until);
                $usage->used = 0;
            }
        }

        $usage->used = ($incremental ? $usage->used + $uses : $uses);

        $usage->save();

        return $usage;
    }

    /**
     * Reduce usage.
     *
     * @param string $featureTag
     * @param int $uses
     *
     * @return \Bpuig\Subby\Models\PlanSubscriptionUsage|null
     */
    public function reduceFeatureUsage(string $featureTag, int $uses = 1): ?PlanSubscriptionUsage
    {
        $usage = $this->getUsageByFeatureTag($featureTag);

        if (!$usage) {
            return null;
        }

        $usage->used = max($usage->used - $uses, 0);

        $usage->save();

        return $usage;
    }

    /**
     * Determine if the feature can be used.
     *
     * @param string $featureTag
     *
     * @return bool
     */
    public function canUseFeature(string $featureTag): bool
    {
        // If subscription is not active (on trial or on period before end date), cannot use
        if (!$this->isActive()) {
            return false;
        }

        $featureValue = $this->getFeatureValue($featureTag);

        if ($featureValue === 'true') {
            // If feature value exists and has a written "true" value
            return true;
        } elseif (is_null($featureValue) || $featureValue === '0' || $featureValue === 'false') {
            // If feature does not exist, it's 0 or written "false"
            return false;
        }

        // Now that we know feature exists in plan, and does not meet any of
        // previous requirements, check for usage
        $usage = $this->getUsageByFeatureTag($featureTag);

        if (!$usage) {
            // If feature usage does not exist, it means it has never been used
            // so subscriber has all usage available, since usage is inserted by recordFeatureUsage
            return true;
        } elseif ($usage->hasExpired()) {
            return false;
        }

        // Check for available uses
        return $this->getFeatureRemainings($featureTag) > 0;
    }

    /**
     * Get how many times the feature has been used.
     *
     * @param string $featureTag
     *
     * @return int
     */
    public function getFeatureUsage(string $featureTag): int
    {
        $usage = $this->getUsageByFeatureTag($featureTag);

        return (!$usage || $usage->hasExpired()) ? 0 : $usage->used;
    }

    /**
     * Get feature usage
     *
     * @param string $featureTag
     *
     * @return mixed
     */
    private function getUsageByFeatureTag(string $featureTag)
    {
        return $this->usage()->byFeatureTag($featureTag)->first();
    }

    /**
     * Get the available uses.
     *
     * @param string $featureTag
     *
     * @return int
     */
    public function getFeatureRemainings(string $featureTag): int
    {
        return $this->getFeatureValue($featureTag) - $this->getFeatureUsage($featureTag);
    }

    /**
     * Get feature value.
     *
     * @param string $featureTag
     *
     * @return mixed
     */
    public function getFeatureValue(string $featureTag)
    {
        $feature = $this->features()->where('tag', $featureTag)->first();

        return $feature->value ?? null;
    }

    /**
     * Get subscription duration in days
     * @return int
     */
    public function getTotalDurationInDays(): int
    {
        return Carbon::make($this->starts_at)->diffInDays($this->ends_at);
    }

    /**
     * Days until subscription renews
     * @return int
     */
    public function getDaysUntilEnds(): int
    {
        return Carbon::now()->diffInDays($this->ends_at);
    }

    /**
     * Days until subscription trial ends
     * @return int
     */
    public function getDaysUntilTrialEnds(): int
    {
        return Carbon::now()->diffInDays($this->trial_ends_at);
    }

    /**
     * Get the proportion of the remaining billing period
     * @return float
     */
    public function getRemainingPeriodProportion(): float
    {
        return round($this->getDaysUntilEnds() / $this->getTotalDurationInDays(), 4);
    }

    /**
     * Get prorated price of subscription value
     * @return float
     */
    public function getRemainingPriceProrate(): float
    {
        return round($this->price * $this->getRemainingPeriodProportion(), 2);
    }
}
