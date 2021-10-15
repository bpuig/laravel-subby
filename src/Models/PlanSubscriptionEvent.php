<?php

declare(strict_types=1);

namespace Bpuig\Subby\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class PlanSubscriptionEvent
 * @package Bpuig\Subby\Models
 */
class PlanSubscriptionEvent extends Model {
    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'tag',
        'eventable_type',
        'eventable_id',
        'description',
        'failed_at',
        'succeeded_at'
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'tag' => 'string',
        'eventable_type' => 'string',
        'eventable_id' => 'integer',
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

        $this->setTable(config('subby.tables.plan_subscription_events'));
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
            'eventable_id' => 'required|integer',
            'description' => 'nullable|string|max:32768'
        ];
    }

    /**
     * Get the parent eventable model (schedule or renewal).
     */
    public function eventable()
    {
        return $this->morphTo();
    }

    /**
     * Get child actions of the events
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function actions() {
        return $this->hasMany(config('subby.models.plan_subscription_event_action'), 'plan_subscription_event_id', 'id');
    }
}
