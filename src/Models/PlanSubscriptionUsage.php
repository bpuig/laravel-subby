<?php

declare(strict_types=1);

namespace Bpuig\Subby\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanSubscriptionUsage extends Model
{

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'plan_subscription_feature_id',
        'used',
        'valid_until',
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'used' => 'integer',
        'valid_until' => 'datetime',
    ];

    /**
     * Create a new Eloquent model instance.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('subby.tables.plan_subscription_usage'));
    }

    /**
     * Get validation rules
     * @return string[]
     */
    public function getRules(): array
    {
        return [
            'plan_subscription_feature_id' => 'required|integer|exists:' . config('subby.tables.plan_features') . ',id',
            'used' => 'required|integer',
            'valid_until' => 'nullable|date',
        ];
    }

    /**
     * Subscription usage always belongs to a plan subscription feature.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function feature(): BelongsTo
    {
        return $this->belongsTo(config('subby.models.plan_subscription_feature'), 'plan_subscription_feature_id', 'id', 'feature');
    }

    /**
     * Scope subscription usage by feature tag.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param string $featureTag
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByFeatureTag(Builder $builder, string $featureTag): Builder
    {
        return $builder->whereHas('feature', function (Builder $query) use ($featureTag) {
            $query->where('tag', $featureTag);
        });
    }

    /**
     * Check whether usage has been expired or not.
     *
     * @return bool
     */
    public function hasExpired(): bool
    {
        if (is_null($this->valid_until)) {
            return false;
        }

        return Carbon::now()->gte($this->valid_until);
    }
}
