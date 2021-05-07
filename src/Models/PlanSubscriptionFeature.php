<?php

declare(strict_types=1);

namespace Bpuig\Subby\Models;

use Bpuig\Subby\Traits\HasResetDate;
use Illuminate\Database\Eloquent\Model;

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
        'subscription_id',
        'feature_id',
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
        'subscription_id' => 'integer',
        'feature_id' => 'integer',
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
                'max:150'
            ],
            'subscription_id' => 'required|integer|exists:' . config('subby.tables.plan_subscriptions') . ',id',
            'feature_id' => 'nullable|integer',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string|max:32768',
            'value' => 'required|string',
            'resettable_period' => 'sometimes|integer',
            'resettable_interval' => 'sometimes|in:hour,day,week,month',
            'sort_order' => 'nullable|integer|max:100000',
        ];
    }


    /**
     * The feature belongs to one subscription.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function subscription()
    {
        return $this->belongsTo(config('subby.models.plan_subscription'), 'subscription_id', 'id');
    }

    /**
     * The feature belongs to one feature.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function feature()
    {
        return $this->belongsTo(config('subby.models.plan_feature'), 'feature_id', 'id');
    }
}
