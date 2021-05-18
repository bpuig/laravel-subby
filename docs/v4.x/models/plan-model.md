# Plan Model

This is the main model of the package, there is nothing without plans. After creating a plan, you
can [attach it to a subscription](plan-subscription-model.md#create-a-subscription).

## Create a Plan

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

## Get Plan details

You can query the plan for further details as follows:

```php
$plan = Plan::find(1);

// Or querying by tag
$plan = Plan::getByTag('basic');

// Get all plan features                
$plan->features;

// Get all plan subscriptions
$plan->subscriptions;

// Check if the plan is free
$plan->isFree();

// Check if the plan has trial period
$plan->hasTrial();

```

Both `$plan->features` and `$plan->subscriptions` are collections, driven from relationships, and thus you can query
these relations as any normal Eloquent relationship. E.g. `$plan->features()->where('tag', 'social_profiles')->first()`
or `$plan->getFeatureByTag('social_profiles')`.

Also read:

- [Get Plan Feature value](plan-feature-model.md#get-plan-feature-value)

## Tiers

The use of tiers is **optional**. Usually a tier is a "level" of subscription.

It helps with upgrading or downgrading because usually an upgrade is changed, billed and renewed instantly, and a
downgrade is changed and billed at the end of period (
see [laravel-subby-schedule](https://github.com/bpuig/laravel-subby-schedule)).

### Example

The way it's thought is:

You have 3 plans: **Basic**, **Intermediate** and **Pro**. How do you now which is better than the other? You can look
at the price.

But... what if there is a promo during some time and the price of **Intermediate** is lower now than **Basic** will be
next month when there is no promo? When you change the subscription plan from your promo **Intermediate** to **Basic**
normally would be a downgrade, but now prices are reversed and action is an upgrade. Weird, huh?

What if you customize your user subscription and now its somewhere in the middle between **Intermediate** and **Pro**?
You can change the tier to a number in between, so you know what to do when changing (downgrading) to existing
Intermediate or upgrading to Pro.

Comparing tier numbers, you can know which subscription or plan is superior to another.

```php
// Example comparing current plan subscription to another plan

if ($user->subscription('main')->tier < $newPlan->tier) {
    myUpgradeFunction();
} else {
    myDowngradeFunction();
}
```
