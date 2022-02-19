# Plan Feature Model

[[toc]]

This model relates to which features has a Plan. This features will be attached to every new subscription.

## Create plan features

Features are things that your plan allows subscribers to do. The obligatory fields are: `tag`, `name`, and `value`. The
value of the feature tells if or how many times the subscriber is allowed to use it. When setting a feature that should
be active or not, use written strings 'true' or 'false', otherwise use an integer to tell how many times it can be used.
If in the higher plan you want to give unlimited access to the "counted" feature, use 'true' as well. If you want the
usage of features to be reset on a regular basis, provide optional `resettable_period` and `resettable_interval` fields.

```php
use Bpuig\Subby\Models\Plan;

$plan = Plan::find(1);

// Create multiple plan features at once
$plan->features()->saveMany([
    new PlanFeature(['tag' => 'social_profiles', 'name' => 'Social profiles available', 'value' => 3, 'sort_order' => 1]),
    new PlanFeature(['tag' => 'posts_per_social_profile', 'name' => 'Scheduled posts per profile', 'value' => 30, 'sort_order' => 10, 'resettable_period' => 1, 'resettable_interval' => 'month']),
    new PlanFeature(['tag' => 'analytics', 'name' => 'Analytics', 'value' => true, 'sort_order' => 15])
]);
```

### Sort order
`sort_order` column has no logic in package, it is just a field for you to use in your queries. 

## Get Plan Feature value

Say you want to show the value of the feature _posts_per_social_profile_ from above. You can do so in many ways:

```php
use Bpuig\Subby\Models\Plan;
use Bpuig\Subby\Models\PlanFeature;
use Bpuig\Subby\Models\PlanSubscription;

$plan = Plan::find(1);

// Use the plan instance to get feature's value
$amountOfPosts = $plan->getFeatureByTag('posts_per_social_profile')->value;

// Query the feature itself directly
$amountOfPosts = PlanFeature::where('tag', 'posts_per_social_profile')->first()->value;

// Get feature value through the subscription instance
$amountOfPosts = PlanSubscription::find(1)->getFeatureValue('posts_per_social_profile');
```

## Feature Options

Plan features are great for fine-tuning subscriptions, you can top up certain feature for X times of usage, so users may
then use it only for that amount. Features also have the ability to be resettable and then it's usage could be expired
too. See the following examples:

```php
use Bpuig\Subby\Models\PlanFeature;

// Find plan feature
$feature = PlanFeature::where('tag', 'posts_per_social_profile')->first();

// Get feature reset date
$feature->getResetDate(new \Carbon\Carbon());
```
