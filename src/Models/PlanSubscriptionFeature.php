<?php

declare(strict_types=1);

namespace Bpuig\Subby\Models;

use Bpuig\Subby\Traits\HasResetDate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

/**
 * Class PlanSubscriptionFeature
 * @package Bpuig\Subby\Models
 */
class PlanSubscriptionFeature extends Model
{
    use HasResetDate;

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'tag',
        'plan_subscription_id',
        'plan_feature_id',
        'name',
        'description',
        'value',
        'resettable_period',
        'resettable_interval',
        'sort_order',
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'tag' => 'string',
        'value' => 'string',
        'resettable_period' => 'integer',
        'resettable_interval' => 'string',
        'sort_order' => 'integer',
    ];

    /**
     * Create a new Eloquent model instance.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('subby.tables.plan_subscription_features'));
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
                'max:150',
                Rule::unique(config('subby.tables.plan_subscription_features'))->where(function ($query) {
                    return $query->where('id', '!=', $this->id)->where('plan_subscription_id', $this->plan_subscription_id);
                }),
            ],
            'plan_subscription_id' => 'required|integer|exists:' . config('subby.tables.plan_subscriptions') . ',id',
            'plan_feature_id' => 'nullable|integer',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string|max:32768',
            'value' => 'required|string',
            'resettable_period' => 'sometimes|integer',
            'resettable_interval' => 'sometimes|in:hour,day,week,month',
            'sort_order' => 'nullable|integer|max:100000',
        ];
    }


    /**
     * The subscription feature belongs to one subscription.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function subscription()
    {
        return $this->belongsTo(config('subby.models.plan_subscription'), 'plan_subscription_id', 'id');
    }

    /**
     * The subscription feature belongs to one plan feature.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function feature()
    {
        return $this->belongsTo(config('subby.models.plan_feature'), 'plan_feature_id', 'id');
    }

    /**
     * The subscription feature has one usage
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function usage()
    {
        return $this->hasOne(config('subby.models.plan_subscription_usage'), 'plan_subscription_feature_id', 'id');
    }

    /**
     * Show features that are not inherited by subscription's plan relation
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithoutPlan(Builder $query)
    {
        return $query->whereHas('feature', function (Builder $query) {
            $query->whereNull('plan_id');
        })
            ->orWhereNull('plan_feature_id');
    }

    /**
     * Sync feature with subscription related plan
     * @return $this
     */
    public function syncPlanSubscription()
    {
        $planFeature = $this->subscription->plan->getFeatureByTag($this->tag);
        $this->syncPlanFeature($planFeature);

        return $this;
    }

    /**
     * Sync feature with related plan feature
     * @param PlanFeature|null $planFeature
     * @return PlanSubscriptionFeature
     */
    public function syncPlanFeature(PlanFeature $planFeature = null): PlanSubscriptionFeature
    {
        if (!$planFeature && $this->plan_feature_id) {
            // If no Plan Feature specified, use plan in related feature (feature_id)
            $planFeature = $this->feature;
        } elseif (!$planFeature && !$this->plan_feature_id) {
            // There is no way to synchronize with a plan
            throw new InvalidArgumentException('Feature is not related to a plan.');
        }

        $this->plan_feature_id = $planFeature->id;
        $this->name = $planFeature->name;
        $this->description = $planFeature->description;
        $this->value = $planFeature->value;
        $this->resettable_period = $planFeature->resettable_period;
        $this->resettable_interval = $planFeature->resettable_interval;
        $this->sort_order = $planFeature->sort_order;

        $this->save();

        return $this;
    }
}
