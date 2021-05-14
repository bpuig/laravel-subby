# Plan Model

This is the main model of the package, there is nothing without plans.

## Create a Plan<a name="create-plan"></a>

```php
use Bpuig\Subby\Models\Plan;

$plan = Plan::create([
    'tag' => 'basic',
    'name' => 'Basic Plan',
    'description' => 'For small businesses',
    'price' => 9.99,
    'signup_fee' => 1.99,
    'invoice_period' => 1,
    'invoice_interval' => 'month',
    'trial_period' => 15,
    'trial_interval' => 'day',
    'tier' => 1,
    'currency' => 'EUR',
]);
```

## Get Plan details<a name="get-plan-details"></a>

You can query the plan for further details as follows:

```php
$plan = Plan::find(1);

// Get all plan features                
$plan->features;

// Get all plan subscriptions
$plan->subscriptions;

// Check if the plan is free
$plan->isFree();

// Check if the plan has trial period
$plan->hasTrial();

// Check if the plan has grace period
$plan->hasGrace();
```

Both `$plan->features` and `$plan->subscriptions` are collections, driven from relationships, and thus you can query
these relations as any normal Eloquent relationship. E.g. `$plan->features()->where('tag', 'social_profiles')->first()`.

Also read:

- [Get Plan Feature value](plan-feature-model.md)
