# Plan Subscription Model

[[toc]]

## Create a Subscription
::: tip New in v6.0
`newSubscription` accepts a `PlanCombination` as second argument
`newSubscription` accepts sixth argument: Payment Method
:::
You can subscribe a user (or any model correctly traited) to a plan by using the `newSubscription()` function available
in the `HasSubscriptions` trait. First, retrieve an instance of your subscriber's model, which typically will be your
user model and an instance of the plan your subscriber is subscribing to. Once you have retrieved the model instance,
you may use the `newSubscription` method to create the model's subscription.

The subscription is made as a _snapshot_ using current plan details as a template. Same happens with subscription
features. When you create a subscription a copy of the Plan Features is made into your Plan Subscription Features.

If related plan is modified in the future, subscriber's subscription stays as it was, price, invoicing and features
are "frozen" unless
[manually synchronized](plan-subscription-model.md#revert-overridden-plan-subscription-features) with related plan.

```php
$user = User::find(1);
$plan = Plan::find(1);

$user->newSubscription(
            'main', // identifier tag of the subscription. If your application offers a single subscription, you might call this 'main' or 'primary'
             $plan, // Plan or PlanCombination instance your subscriber is subscribing to
             'Main subscription', // Human-readable name for your subscription
             'Customer main subscription', // Description
             null, // Start date for the subscription, defaults to now()
             'free' // Payment method service defined in config
             );
```

## Change its Plan

You can change subscription related plan easily as follows, method accepts either `Plan` or `PlanCombination`:

```php
$plan = Plan::find(2);
$subscription = PlanSubscription::find(1);

// Change subscription plan clearing usage and synchronizing invoicing periods
$subscription->changePlan($plan);

// Change subscription plan and keep usage
$subscription->changePlan($plan, false);

// Change subscription plan, keep usage and invoicing data
$subscription->changePlan($plan, false, false);
```

Subscription usage data will be cleared by default, unless `false` is given as second parameter.

If you want the same billing frequency (`invoice_period` and `invoice_interval`) set third parameter to `true`
and subscription will inherit plan's billing frequency. If you want to keep current subscription invoice intervals, set
to `false`. By default, invoice details are synchronized with new plan.

- Plan change will adjust existing features to the ones in the new plan.
- Change will also remove features attached to old plan.
- Existent features that where previously attached without plan but exist in the new plan now will use plan values.
- Features not attached to a plan and nonexistent in new plan will remain the same.

## Change pricing and other details

You can change the price or details without affecting attached features or plan. With this feature you can set prices
individually for every subscriber.

```php 
$subscription = $user->subscription('main');

$subscription->description = 'Main description with great discount';
$subscription->price = 12;

$subscription->save();
```

### Revert custom subscription changes (resynchronize to plan)

You can revert changes made to subscription with function `syncPlan`. Be careful if you are using a `PlanCombination`,
synchronizing without specifying a plan will synchronize subscription with parent plan.

```php 
// Synchronize price, invoicing and tier with parent plan
$user->subscription('main')->syncPlan();

// Synchronize with parent plan and also features
$user->subscription('main')->syncPlan(null, true, true);

```

`syncPlan()` accepts 3 parameters. 
- First parameter is a `Plan`, if you want to synchronize with current plan
(default behaviour), set to `null`. 
- Second is `bool` for synchronizing also invoicing details (period and interval),
default behaviour is to synchronize `true`. 
- Third one is `bool` to also synchronize features.

## Grace

Grace period is the extra time the subscription will be considered active after it has ended.

### Grace related functions

```php
$user->subscription('main')->hasGrace(); // Returns boolean indicating if subscription has grace period
$user->subscription('main')->getGraceTotalDurationIn('day'); // Returns duration integer in set Carbon interval (second, day, month...)
$user->subscription('main')->getGraceStartDate(); // Returns grace start date (a.k.a. subscription end date)
$user->subscription('main')->getGraceEndDate(); // Returns grace end date
$user->subscription('main')->getGraceTotalDurationIn('day'); // Returns number of days grace lasts
$user->subscription('main')->getGracePeriodUsageIn('day'); // Returns number of days of grace consumed
$user->subscription('main')->getGracePeriodRemainingUsageIn('day'); // Returns number of days until subscription grace ends
$user->subscription('main')->hasStartedGrace(); // Returns boolean indicating if grace period is over
$user->subscription('main')->hasEndedGrace(); // Returns boolean indicating if grace period is over
```

## Subscriber's subscriptions

Retrieve subscriptions of subscriber.

```php
// Get user subscriptions
$user->subscriptions;

// Get user active subscriptions
$user->activeSubscriptions;
```

## Subscriber's main or only subscription

Since usually projects work with only one subscription or one primary, you have to set the tag for it in the
config `main_subscription_tag`.

If your user only has one subscription, `subscription()` will return the only one the subscriber has. If has more, it
will fall to default. Default is `main`.

```php
// config/subby.php
return [
    'main_subscription_tag' => 'main',
    ...
];
````

Then:

```php
// This retrieves user's only one subscription or 'main' from config:
$user->subscription();
```

## Subscription Feature Usage

You can determine the usage and ability of a particular feature in the subscriber's subscription with `canUseFeature`:

The `canUseFeature` method returns `true` or `false` depending on multiple factors:

- Subscription is active (on trial or currently in period).
- Feature _is enabled_ (`true`).
- Feature value isn't `0`/`false`/`NULL`.
- Feature has remaining uses available.

```php
$user->subscription('main')->canUseFeature('social_profiles');
```

Other feature methods on the user subscription instance are:

- `getFeatureUsage`: returns how many times the user has used a particular feature.
- `getFeatureRemainings`: returns available uses for a particular feature.
- `getFeatureValue`: returns the feature value.

All methods share the same signature: e.g. `$user->subscription('main')->getFeatureUsage('social_profiles');`.

### Record Feature Usage

In order to effectively use the ability methods you will need to keep track of every usage of each feature (or at least
those that require it). You may use the `recordFeatureUsage` method available through the user `subscription()` method:

```php
$user->subscription('main')->recordFeatureUsage('social_profiles');
```

When recording feature `canUseFeature` is already called within the function, so you do not have to check every time.
`UsageDenied` Exception is thrown if subscriber cannot use the feature.

The `recordFeatureUsage` method accepts 3 parameters: the first one is the feature's tag, the second one is the quantity
of uses to add (default is `1`), and the third one indicates if the addition should be incremental (default behavior),
when disabled the usage will be overridden by the quantity provided.

::: details Click me to view example code

```php
// Increment by 1
$user->subscription('main')->recordFeatureUsage('social_profiles', 1);

// Override with 3
$user->subscription('main')->recordFeatureUsage('social_profiles', 3, false);
```

:::

### Reduce Feature Usage

Reducing the feature usage is _almost_ the same as incrementing it. Here we only _substract_ a given quantity (default
is `1`) to the actual usage:

```php
$user->subscription('main')->reduceFeatureUsage('social_profiles', 2);
```

### Clear the Subscription Usage data

```php
$user->subscription('main')->usage()->delete();
```

## Check Subscription status

For a subscription to be considered active one of the following must be `true`:

- Subscription has an active trial.
- Subscription `ends_at` is in the future.

Alternatively you can use the following methods available in the subscription model:

```php
$user->subscription('main')->isActive();
$user->subscription('main')->isCanceled();
$user->subscription('main')->hasEnded();
$user->subscription('main')->hasEndedTrial();
$user->subscription('main')->isOnTrial();

// To know if subscription has the same values as related plan or has been changed
$user->subscription('main')->isAltered();
```

### Remaining price prorate

You can get the remaining prorated amount until subscription invoice period ends.

```php
$user->subscription('main')->getSubscriptionRemainingUsagePriceProrate(); // Ex: 10 day subscription of price 10.00, on day 6, returns 4
```

### Trial period time related functions

You can get some information about duration in your trial with:

```php
$user->subscription('main')->getTrialStartDate(); // When did the trial start
$user->subscription('main')->getTrialTotalDurationIn('day'); // Returns number of days trial lasts
$user->subscription('main')->getTrialPeriodUsageIn('day'); // Returns number of days of trial consumed
$user->subscription('main')->getTrialPeriodRemainingUsageIn('day'); // Returns number of days until subscription trial ends
```

You can use Carbon accepted intervals (in singular): `year`,`month`,`day`,`hour`,`minute`,`second`,`microsecond`...

### Subscription period time related functions

You can get some information about duration in your subscription with:

```php
$user->subscription('main')->getSubscriptionTotalDurationIn('day'); // Returns number of days subscription lasts
$user->subscription('main')->getSubscriptionPeriodUsageIn('day'); // Returns number of days of subscription consumed
$user->subscription('main')->getSubscriptionPeriodRemainingUsageIn('day'); // Returns number of days until subscription ends
```

You can use Carbon accepted intervals (in singular): `year`,`month`,`day`,`hour`,`minute`,`second`,`microsecond`...

## Revert overridden plan subscription features

You can revert all feature changes made to subscription that are related to a plan.

```php 
// Resynchronize features
$user->subscription('main')->syncPlanFeatures();
```

Now all plan features available in subscription's related plan will be reset in subscription feature. If your subscriber
has attached manually a feature that was not previously available in plan, but now is, your custom subscription feature
will be related to plan feature and will be overridden with plan's feature details in this synchronization.

### Other

```php 
// Check if subscription is free
$user->subscription('main')->isFree();

// Check subscriber to plan
$user->isSubscribedTo($planId);
```

Canceled subscriptions with an active trial or `ends_at` in the future are considered active.

## Renew a Subscription

To renew a subscription you may use the `renew` method available in the subscription model. This will set a
new `ends_at` date based on the selected plan.

```php
$user->subscription('main')->renew();

$user->subscription('main')->renew(3); // This will triple the periods. CAUTION: If your subscription is 2 'month', you'll get 6 'month'
```

Canceled subscriptions can't be renewed. Renewing a subscription with trial period ends it.

When a subscription has already ended time ago and now is renewed, period will be set as if subscription started today,
but when a subscription is still ongoing and renewed, start date is kept and end date is extended by the amount of
periods specified.

## Cancel a Subscription

To cancel a subscription, simply use the `cancel` method on the user's subscription:

```php
$user->subscription('main')->cancel();
```

### Immediately

By default the subscription will remain active until the end of the period, you may pass `true` to end the
subscription _immediately_:

```php
$user->subscription('main')->cancel(true);
```

### Fallback plan

If a `fallback_plan_tag` is not `null` in config, when `cancel` is called, subscription will not be canceled but changed
to fallback plan.

To cancel subscription and ignore fallback, a second parameter is available on `cancel` method:

```php
$user->subscription('main')->cancel(false, true);
```

## Uncancel a subscription

To uncancel a subscription, simply use the `uncancel` method on the user's subscription:

```php
$user->subscription('main')->uncancel();
```

## Scopes

```php
// Get subscriptions by plan
$subscriptions = PlanSubscription::byPlanId($planId)->get();

// Get bookings of the given user
$user = \App\Models\User::find(1);
$bookingsOfUser = PlanSubscription::ofSubscriber($user)->get(); 

// Get subscriptions with trial ending in 3 days
$subscriptions = PlanSubscription::findEndingTrial(3)->get();

// Get subscriptions with ended trial
$subscriptions = PlanSubscription::findEndedTrial()->get();

// Get subscriptions with period ending in 3 days
$subscriptions = PlanSubscription::findEndingPeriod(3)->get();

// Get subscriptions with ended period
$subscriptions = PlanSubscription::findEndedPeriod()->get();

// Get subscriptions with period ending in 3 days filtered by the subscription tag
$subscriptions = PlanSubscription::getByTag('company')->findEndingPeriod(3)->get();
```
