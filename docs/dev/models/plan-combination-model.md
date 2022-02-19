# Plan Combination Model

[[toc]]

With this model you define your plan combinations. You can have multiple prices and intervals per currency, country,
etc.

## Create a Plan Combination

```php
use Bpuig\Subby\Models\Plan;
use Bpuig\Subby\Models\PlanCombination;

$plan = Plan::getByTag('basic');

$plan->combinations()->create([
    'tag' => 'basic-es-eur-1-year'
    'country' => 'ES',
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

$planCombination = $plan->combinations()
                                        ->where('country', 'ES')
                                        ->where('currency', 'EUR')
                                        ->where('invoice_period', 1)
                                        ->where('invoice_interval', 'year')
                                        ->first();

// Get parent plan                
$planCombination->plan;

```
