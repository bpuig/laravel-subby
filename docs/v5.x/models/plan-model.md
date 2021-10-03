# Plan Model

[[toc]]

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
    'grace_period' => 1,
    'grace_interval' => 'day',
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

## Trial modes

There are two available trial modes: `inside` or `outside`. This defines how the trial will be counted when renewal time
is due.

**USAGE WILL NOT BE CLEARED** when user has had trial time. This is what gives sense to both methods.

When a **new subscription** to a plan is made:

### If plan has trial

If plan has trial, subscriber does not have subscription but only a trial. Subscription period starts and ends at `null`
and this is considered subscription is not made. Because in a real case scenario, when a subscriber has a trial it does
not have a subscription yet, so the invoice period is made and charged after the trial has ended.

#### Renewal when trial is "inside"

If trial mode is `inside`; when trial ends and is renewed invoice period will have substracted the days of trial that
have been used.

*Example:* 7 day trial in a 30 day subscription period.

- User uses 3 days, likes the app them and renews the subscription. **Result:** The next subscription renewal will be in
  27 days.
- User uses all 7 day trial. Forgets about the app and comes back a week later. **Result:** The next subscription
  renewal will be in 23 days.

In summary: this is **NOT** a free trial. User always ends up paying the full price for full period.

#### Renewal when trial is "outside"

If trial mode is `outside`; when trial ends and is renewed, invoice period will start at the moment it's renewed.

*Example:* 7 day trial in a 30 day subscription period.

- User uses 3 days, likes the app them and renews the subscription. **Result:** The next subscription renewal will be in
  30 days. User got 3 days for free.
- User uses all 7 day trial. Forgets about the app and comes back a week later. **Result:** The next subscription
  renewal will be in 30 days. User got 7 days for free.

In summary: this is **IS** a free trial. User does not pay for the trial period, but for the next subscription period.

### If plan does not have trial

If plan does not have trial, subscriber has subscription. Because when a plan does not have trial, a new subscription
activates a new invoicing period.

### Trial period time related functions <Badge text="new in v5.0" type="tip"/>

You can get some information about duration of your trial with:

```php
$plan->getTrialTotalDurationIn('day'); // Returns number of days trial lasts
```

You can use Carbon accepted intervals (in singular): `year`,`month`,`day`,`hour`,`minute`,`second`,`microsecond`...

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

What if you customize your user subscription and now it is somewhere in the middle between **Intermediate** and **Pro**?
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

## Grace <Badge text="new in v5.0" type="tip"/>
Grace period is the extra time the subscription will be considered active after it has ended. By default is disabled, 
you can set it when creating the plan with a `grace_period` and `grace_interval`. It will be inherited by new subscriptions
and also will be synchronized when using `syncPlan`.

### Grace related functions
```php
$plan->hasGrace(); // Returns boolean indicating if plan has grace period
$plan->getGraceTotalDurationIn('day'); // Returns duration integer in set Carbon interval (second, day, month...)
```

## Subscription period time related functions <Badge text="new in v5.0" type="tip"/>

You can get some information about duration of the subscription with:

```php
$plan->getSubscriptionTotalDurationIn('day'); // Returns number of days subscription lasts
```

You can use Carbon accepted intervals (in singular): `year`,`month`,`day`,`hour`,`minute`,`second`,`microsecond`...
