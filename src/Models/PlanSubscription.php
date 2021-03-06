<?php

declare(strict_types=1);

namespace Bpuig\Subby\Models;

use Bpuig\Subby\Services\Period;
use Bpuig\Subby\Traits\BelongsToPlan;
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
    use BelongsToPlan;

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'tag',
        'user_id',
        'user_type',
        'plan_id',
        'name',
        'description',
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
        'user_id' => 'integer',
        'user_type' => 'string',
        'plan_id' => 'integer',
        'trial_ends_at' => 'datetime',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'cancels_at' => 'datetime',
        'canceled_at' => 'datetime',
        'deleted_at' => 'datetime',
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
                    return $query->where('id', '!=', $this->id)->where('user_type', $this->user_type)
                        ->where('user_id', $this->user_id);
                }),
            ],
            'name' => 'required|string|strip_tags|max:150',
            'description' => 'nullable|string|max:32768',
            'plan_id' => 'required|integer|exists:' . config('subby.tables.plans') . ',id',
            'user_id' => 'required|integer',
            'user_type' => 'required|string|strip_tags|max:150',
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
     * Get the owning user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function user(): MorphTo
    {
        return $this->morphTo('user', 'user_type', 'user_id', 'id');
    }

    /**
     * The subscription may have many usage.
     *
     * @return HasMany
     */
    public function usage(): hasMany
    {
        return $this->hasMany(config('subby.models.plan_subscription_usage'), 'subscription_id', 'id');
    }

    /**
     * Check if subscription is active.
     *
     * @return bool
     */
    public function active(): bool
    {
        return !$this->ended() || $this->onTrial();
    }

    /**
     * Check if subscription is inactive.
     *
     * @return bool
     */
    public function inactive(): bool
    {
        return !$this->active();
    }

    /**
     * Check if subscription is currently on trial.
     *
     * @return bool
     */
    public function onTrial(): bool
    {
        return $this->trial_ends_at ? Carbon::now()->lt($this->trial_ends_at) : false;
    }

    /**
     * Check if subscription is canceled.
     *
     * @return bool
     */
    public function canceled(): bool
    {
        return $this->canceled_at ? Carbon::now()->gte($this->canceled_at) : false;
    }

    /**
     * Check if subscription period has ended.
     *
     * @return bool
     */
    public function ended(): bool
    {
        return $this->ends_at ? Carbon::now()->gte($this->ends_at) : false;
    }

    /**
     * Cancel subscription.
     *
     * @param bool $immediately
     *
     * @return $this
     */
    public function cancel($immediately = false)
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
    public function uncancel()
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
     *
     * @return $this
     */
    public function changePlan(Plan $plan, bool $clearUsage = true)
    {
        // If plans does not have the same billing frequency
        // (e.g., invoice_interval and invoice_period) we will update
        // the billing dates starting today, and since we are basically creating
        // a new billing cycle, the usage data will be cleared.
        if ($this->plan->invoice_interval !== $plan->invoice_interval || $this->plan->invoice_period !== $plan->invoice_period) {
            $this->setNewPeriod($plan->invoice_interval, $plan->invoice_period);
            // Sometimes you want to keep usage
            // E.g. of false: Renew plan at day 6 of subscription,
            // and if you consumed 2 resources, you keep having 2 consumed of
            // the new limit
            if ($clearUsage) {
                $this->usage()->delete();
            }
        }

        // Attach new plan to subscription
        $this->plan_id = $plan->getKey();
        $this->save();

        return $this;
    }

    /**
     * Renew subscription period.
     *
     * @return $this
     * @throws \LogicException
     *
     */
    public function renew()
    {
        if ($this->canceled()) {
            throw new LogicException('Unable to renew canceled subscription.');
        }

        $subscription = $this;

        DB::transaction(function () use ($subscription) {
            // Clear usage data
            $subscription->usage()->delete();

            // Renew period
            $subscription->setNewPeriod();
            $subscription->save();
        });

        return $this;
    }

    /**
     * Get bookings of the given user.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param \Illuminate\Database\Eloquent\Model $user
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfUser(Builder $builder, Model $user): Builder
    {
        return $builder->where('user_type', $user->getMorphClass())->where('user_id', $user->getKey());
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
     * Set new subscription period.
     *
     * @param string $invoice_interval
     * @param string $invoice_period
     * @param string $start
     *
     * @return $this
     */
    protected function setNewPeriod($invoice_interval = '', $invoice_period = '', $start = '')
    {
        if (empty($invoice_interval)) {
            $invoice_interval = $this->plan->invoice_interval;
        }

        if (empty($invoice_period)) {
            $invoice_period = $this->plan->invoice_period;
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
     * @return \Bpuig\Subby\Models\PlanSubscriptionUsage
     */
    public function recordFeatureUsage(
        string $featureTag,
        int $uses = 1,
        bool $incremental = true
    ): PlanSubscriptionUsage
    {
        $feature = $this->plan->features()->where('tag', $featureTag)->first();

        $usage = $this->usage()->firstOrNew([
            'subscription_id' => $this->getKey(),
            'feature_id' => $feature->getKey(),
        ]);

        if ($feature->resettable_period) {
            // Set expiration date when the usage record is new or doesn't have one.
            if (is_null($usage->valid_until)) {
                // Set date from subscription creation date so the reset
                // period match the period specified by the subscription's plan.
                $usage->valid_until = $feature->getResetDate($this->created_at);
            } elseif ($usage->expired()) {
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
        $usage = $this->usage()->byFeatureTag($featureTag)->first();

        if (is_null($usage)) {
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
        // If subscription has ended, cannot use
        if ($this->ended()) {
            return false;
        }

        $featureValue = $this->getFeatureValue($featureTag);

        if ($featureValue === 'true') {
            // If feature value exists and has a written true value
            return true;
        } elseif (is_null($featureValue) || $featureValue === '0' || $featureValue === 'false') {
            // If feature does not exist, it's 0 or written false
            return false;
        }

        // Now that we know feature exists in plan, and does not meet any of
        // previous requirements, check for usage
        $usage = $this->usage()->byFeatureTag($featureTag)->first();

        if (is_null($usage)) {
            // If feature usage does not exist, it means it has never been used
            // so user has all usage available, since usage is inserted by recordFeatureUsage
            return true;
        } elseif ($usage->expired()) {
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
        $usage = $this->usage()->byFeatureTag($featureTag)->first();

        return !$usage->expired() ? $usage->used : 0;
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
        $feature = $this->plan->features()->where('tag', $featureTag)->first();

        return $feature->value ?? null;
    }
}
