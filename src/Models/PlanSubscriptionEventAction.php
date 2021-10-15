<?php

declare(strict_types=1);

namespace Bpuig\Subby\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class PlanSubscriptionEventAction
 * @package Bpuig\Subby\Models
 */
class PlanSubscriptionEventAction extends Model {
    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'tag',
        'plan_subscription_event_id',
        'description',
        'failed_at',
        'succeeded_at'
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'tag' => 'string',
        'plan_subscription_event_id' => 'integer',
        'description' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'failed_at' => 'datetime',
        'succeeded_at' => 'datetime'
    ];

    /**
     * Create a new Eloquent model instance.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('subby.tables.plan_subscription_event_actions'));
    }

    /**
     * Get validation rules
     * @return string[]
     */
    public function getRules(): array
    {
        return [
            'tag' => 'required|max:150',
            'eventable_type' => 'required',
            'plan_subscription_event_id' => 'required|integer|exists:' . config('subby.tables.plan_subscription_events') . ',id',
            'description' => 'nullable|string|max:32768'
        ];
    }

    /**
     * Get parent event of the action
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function event() {
        return $this->belongsTo(config('subby.models.plan_subscription_event'), 'plan_subscription_event_id', 'id');
    }
}
