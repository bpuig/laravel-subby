# Plan Subscription Feature Model

This model relates to which features has a Subscription.

## How does it work?

A [subscription](plan-subscription-model.md) has features that can be used. Subscription Features are related but
independent from Plan Features. When a subscription is created or a feature is attached, it makes a copy so it's
decoupled and changes to related plan will not be applied automatically.

## How do Subscription Features relate to Plan Features?

Plan Subscription Feature has two relationships to Plan, if you force it they can be two different plans, usually both
will only lead to one Plan.

### Via Subscription

`PlanSubscriptionFeature` belongs **always** to one `PlanSubscription`, and it belongs **always** to one `Plan`.

### Via Plan Feature

`PlanSubscriptionFeature` **may** belong to one `PlanFeature`, and it belongs **always** to one `Plan`.

## Add features to a subscription

### Inherited from plan when subscribed

Features are assigned and inherited from plan when user is subscribed to a plan. This makes a copy of current plan
features into the subscription.

```php
$user->newSubscription('main', $plan, 'Main subscription', 'Customer main subscription');
```

Now subscriber's subscription will have all current plan features.

### Manually assign without relation to a plan feature

A plan has `social_profiles` feature. When subscriber is subscribed to that plan, the subscription can now
use `social_profiles`.

```php
// You can also attach directly features to user
$user->subscription('main')->features()->create([
    'tag' => 'pictures_per_social_profile', 
    'name' => 'Pictures per social profile', 
    'value' => 30,
    'sort_order' => 10,
    'resettable_period' => 1,
    'resettable_interval' => 'month'
]);
```

Now user can also make use of the `pictures_per_social_profile` feature, and it will be reset monthly.

### Override existing feature values

If subscriber has inherited a feature from a plan, there cannot be two features with the same tag attached to
subscriber. But since subscription features do not depend anymore on plan features, you can override said feature.

```php
// Modify feature limit for subscriber
$user->subscription('main')->getFeatureByTag('pictures_per_social_profile')
    ->update([     
       'value' => 60,
    ]);
```

Just like that, our user will be always capable of using 60 pictures, no matter what the plan feature limit is.

## Revert all changes and sync to plan

You can revert all your changes to a subscription an return to a clean copy of current subscription's Plan Features.

```php
// Resync features with subscription's current plan
$user->subscription('main')->syncPlanFeatures();
```

`syncPlanFeatures` accepts one parameter `Plan`, in case you want a clean copy of another plan that is not current plan.

## Revert one change

You can revert one feature to a subscription and return to a clean copy of current subscription's Plan Feature or
current
`Plan Feature` in case it's not the same.

#### Revert to Subscription's Plan value

It will sync your subscription's feature retrieving your Plan Feature via Plan in Subscription related
in `subscription_id`.

```php
// Resync feature with subscription's current plan
$user->subscription('main')->getFeatureByTag('pictures_per_social_profile')->syncPlanSubscription();
```

#### Revert to Subscription's Feature Plan Feature value

It will sync your subscription's feature retrieving your Plan Feature via Plan Feature in `plan_feature_id`.

```php
// Resync feature with subscription's current plan
$user->subscription('main')->getFeatureByTag('pictures_per_social_profile')->syncPlanFeature();
```

## Retrieve features without plan

If you manually attached features that were not included in related subscription plan, you can retrieve them via scope.

```php 
// Retrieve features that are not tied in any form to a plan
$user->subscription('main')->features()->withoutPlan()->get();
```

## Feature usage

See also [subscription feature usage](models/plan-subscription-model.html#subscription-feature-usage).

Plan subscription feature usage object can be retrieved via `usage()` relationship:

```php
$user->subscription('main')->getFeatureByTag('social_profiles')->usage;
```
To get all the subscription features along with their usage:
```php
$user->subscription('main')->features()->with('usage')->get();
```
