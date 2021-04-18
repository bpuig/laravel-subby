<?php

declare(strict_types=1);

namespace Bpuig\Subby\Models;

use Bpuig\Subby\Services\Period;
use Bpuig\Subby\Traits\BelongsToPlan;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;

class PlanFeature extends Model
{
    use BelongsToPlan;
    use SoftDeletes;

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'tag',
        'plan_id',
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
        'plan_id' => 'integer',
        'value' => 'string',
        'resettable_period' => 'integer',
        'resettable_interval' => 'string',
        'sort_order' => 'integer',
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

        $this->setTable(config('subby.tables.plan_features'));
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
                Rule::unique(config('subby.tables.plan_features'))->where(function ($query) {
                    return $query->where('id', '!=', $this->id)->where('plan_id', $this->plan_id);
                }),
            ],
            'plan_id' => 'required|integer|exists:' . config('subby.tables.plans') . ',id',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string|max:32768',
            'value' => 'required|string',
            'resettable_period' => 'sometimes|integer',
            'resettable_interval' => 'sometimes|in:hour,day,week,month',
            'sort_order' => 'nullable|integer|max:100000',
        ];
    }

    /**
     * The plan feature may have many subscription usage.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function usage(): HasMany
    {
        return $this->hasMany(config('subby.models.plan_subscription_usage'), 'feature_id', 'id');
    }

    /**
     * Get feature's reset date.
     *
     * @param string $dateFrom
     *
     * @return \Carbon\Carbon
     * @throws \Exception
     */
    public function getResetDate(Carbon $dateFrom): Carbon
    {
        $period = new Period($this->resettable_interval, $this->resettable_period, $dateFrom ?? now());

        return $period->getEndDate();
    }
}
