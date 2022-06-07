# Plan Combination Model

[[toc]]

With this model you define your plan combinations. You can have multiple prices and intervals per currency, country,
etc.

## Create a Plan Combination

Combinations must be unique for `country`, `currency`, `invoice_period` and `invoice_interval`.

```php
use Bpuig\Subby\Models\Plan;
use Bpuig\Subby\Models\PlanCombination;

$plan = Plan::getByTag('basic');

$plan->combinations()->create([
    'tag' => 'basic-es-eur-1-year',
    'country' => 'ESP',
    'currency' => 'EUR',
    'price' => 99.99,
    'invoice_period' => 1,
    'invoice_interval' => 'year',
]);
```

## Get Plan Combination details

You can query the plan combination for further details as follows:

```php
$planCombination = PlanCombination::find(1);

// Or querying by tag
$planCombination = PlanCombination::getByTag('basic-es-eur-1-year');

// Or do your own query
$plan = Plan::getByTag('basic');

$planCombination = $plan->combinations()->where('country', 'ESP')
                                        ->where('currency', 'EUR')
                                        ->where('invoice_period', 1)
                                        ->where('invoice_interval', 'year')
                                        ->first();

// Get parent plan                
$planCombination->plan;

```

## Subscribe to plan combination

See [create a Subscription](plan-subscription-model.md#create-a-subscription) and use a `PlanCombination` instead of a
`Plan`.

## Change subscription's plan to plan combination

See [change its plan](plan-subscription-model.md#change-its-plan) and use a `PlanCombination` instead of a `Plan`.

## Schedule subscription's plan change to plan combination

See [create schedule](plan-subscription-schedule-model.md#create-schedule) and use a `PlanCombination` instead of a
`Plan`.
