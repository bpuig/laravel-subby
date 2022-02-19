<?php

declare(strict_types=1);

namespace Bpuig\Subby\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Plan Combination
 * @package Bpuig\Subby\Models
 */
class PlanCombination extends Model
{
    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'tag',
        'country',
        'currency',
        'price',
        'signup_fee',
        'invoice_period',
        'invoice_interval'
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'tag' => 'string',
        'country' => 'string',
        'currency' => 'string',
        'price' => 'float',
        'signup_fee' => 'float',
        'invoice_period' => 'integer',
        'invoice_interval' => 'string'
    ];

    /**
     * Create a new Eloquent model instance.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('subby.tables.plan_combinations'));
    }

    /**
     * Get validation rules
     * @return string[]
     */
    public function getRules(): array
    {
        return [
            'tag' => 'required|max:150|unique:' . config('subby.tables.plan_combinations') . ',tag',
            'country' => 'required|alpha|size:3',
            'price' => 'required|numeric',
            'signup_fee' => 'required|numeric',
            'currency' => 'required|alpha|size:3',
            'invoice_period' => 'sometimes|integer|max:100000',
            'invoice_interval' => 'sometimes|in:hour,day,week,month'
        ];
    }
}
