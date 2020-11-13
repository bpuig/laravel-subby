<img src="art/socialcard.png" alt="Social Card of Laravel Subby">

# Laravel Subby

**Laravel Subby** is a flexible plans and subscription management system for Laravel.
Originally forked from [rinvex/laravel-subscriptions](https://github.com/rinvex/laravel-subscriptions).
## Table of Contents

<details><summary>Click to expand</summary><p>

- [Considerations](#considerations)
- [Installation](#installation)
- [Usage](#usage)
  - [Add Subscriptions to User model](#add-subscription)
  - [Create Plan](#create-plan)
  - [Get Plan details](#get-plan-details)
  - [Get Feature value](#get-feature-value)
  - [Create a Subscription](#create-subscription)
  - [Change the Plan](#change-plan)
  - [Feature options](#feature-options)
  - [Subscription Feature Usage](#subscription-feature-usage)
  - [Record Feature Usage](#record-feature-usage)
  - [Reduce Feature Usage](#reduce-feature-usage)
  - [Check Subscription Status](#check-subscription-status)
  - [Renew a Subscription](#renew-subscription)
  - [Cancel a Subscription](#cancel-subscription)
  - [Scopes](#scopes)
    - [Subscription model](#subscription-model)
  - [Models](#models)
- [Changelog](#changelog)
- [License](#license)
</p>
</details>

## Considerations<a name="considerations"></a>

- Payments and translations are out of scope for this package.
- You may want to extend some core models, in case you need to override the logic behind some helper methods like `renew()`, `cancel()` etc. E.g.: when cancelling a subscription you may want to also cancel the recurring payment attached.


## Installation<a name="installation"></a>

Install the package via composer:
```shell
composer require bpuig/laravel-subby
```
Publish the configuration:
```shell
php artisan vendor:publish --tag=subby.config
```
Publish migrations:
```shell
php artisan vendor:publish --tag=subby.migrations
```
Migrate:
```shell
php artisan migrate
```

## Usage<a name="usage"></a>

### Add Subscriptions to User model<a name="add-subscription"></a>

**Laravel Subby** has been specially made for Eloquent. To add Subscription functionality to your User model just use the `\Bpuig\Subby\Traits\HasSubscriptions` trait like this:

```php
namespace App\Models;

use Bpuig\Subby\Traits\HasSubscriptions;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasSubscriptions;
}
```

That's it, we only have to use that trait in our User model! Now your users may subscribe to plans. Then you can import package's models wherever you need them or extend them in your own models.

### Create a Plan<a name="create-plan"></a>

```php
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

// Create multiple plan features at once
$plan->features()->saveMany([
    new PlanFeature(['tag' => 'social_profiles', 'name' => 'Social profiles available', 'value' => 3, 'sort_order' => 1]),
    new PlanFeature(['tag' => 'posts_per_social_profile', 'name' => 'Scheduled posts per profile', 'value' => 30, 'sort_order' => 10, 'resettable_period' => 1, 'resettable_interval' => 'month']),
    new PlanFeature(['tag' => 'analytics', 'name' => 'Analytics', 'value' => true, 'sort_order' => 15])
]);
```

### Get Plan details<a name="get-plan-details"></a>

You can query the plan for further details, using the intuitive API as follows:

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

Both `$plan->features` and `$plan->subscriptions` are collections, driven from relationships, and thus you can query these relations as any normal Eloquent relationship. E.g. `$plan->features()->where('tag', 'social_profiles')->first()`.

### Get Feature value<a name="get-feature-value"></a>

Say you want to show the value of the feature _posts_per_social_profile_ from above. You can do so in many ways:

```php
// Use the plan instance to get feature's value
$amountOfPosts = $plan->getFeatureByTag('posts_per_social_profile')->value;

// Query the feature itself directly
$amountOfPosts = PlanFeature::where('tag', 'posts_per_social_profile')->first()->value;

// Get feature value through the subscription instance
$amountOfPosts = PlanSubscription::find(1)->getFeatureValue('posts_per_social_profile');
```

### Create a Subscription<a name="create-subscription"></a>

You can subscribe a user to a plan by using the `newSubscription()` function available in the `HasSubscriptions` trait. First, retrieve an instance of your subscriber model, which typically will be your user model and an instance of the plan your user is subscribing to. Once you have retrieved the model instance, you may use the `newSubscription` method to create the model's subscription.

```php
$user = User::find(1);
$plan = Plan::find(1);

$user->newSubscription('main', $plan, 'Main subscription');
```

The first argument passed to `newSubscription` method should be the identifier tag of the subscription. If your application offer a single subscription, you might call this `main` or `primary`. The second argument is the plan instance your user is subscribing to and the third argument is a human readable name for your subscription.

### Change the Plan<a name="change-plan"></a>

You can change subscription plan easily as follows:

```php
$plan = Plan::find(2);
$subscription = PlanSubscription::find(1);

// Change subscription plan
$subscription->changePlan($plan);
```

If both plans (current and new plan) have the same billing frequency (e.g., `invoice_period` and `invoice_interval`) the subscription will retain the same billing dates. If the plans don't have the same billing frequency, the subscription will have the new plan billing frequency, starting on the day of the change.

_Subscription usage data will be cleared_ by default, unless `false` is given as second parameter.

Also, if the new plan has a trial period, and it's a new subscription, the trial period will be applied.

### Feature Options<a name="feature-options"></a>

Plan features are great for fine-tuning subscriptions, you can top up certain feature for X times of usage, so users may then use it only for that amount. Features also have the ability to be resettable and then it's usage could be expired too. See the following examples:

```php
// Find plan feature
$feature = PlanFeature::where('tag', 'posts_per_social_profile')->first();

// Get feature reset date
$feature->getResetDate(new \Carbon\Carbon());
```

### Subscription Feature Usage<a name="subscription-feature-usage"></a>

There are multiple ways to determine the usage and ability of a particular feature in the user subscription, the most common one is `canUseFeature`:

The `canUseFeature` method returns `true` or `false` depending on multiple factors:

- Subscription has not ended.
- Feature _is enabled_.
- Feature value isn't `0`/`false`/`NULL`.
- Or feature has remaining uses available.

```php
$user->subscription('main')->canUseFeature('social_profiles');
```

Other feature methods on the user subscription instance are:

- `getFeatureUsage`: returns how many times the user has used a particular feature.
- `getFeatureRemainings`: returns available uses for a particular feature.
- `getFeatureValue`: returns the feature value.

> All methods share the same signature: e.g. `$user->subscription('main')->getFeatureUsage('social_profiles');`.

### Record Feature Usage<a name="record-feature-usage"></a>

In order to effectively use the ability methods you will need to keep track of every usage of each feature (or at least those that require it). You may use the `recordFeatureUsage` method available through the user `subscription()` method:

```php
$user->subscription('main')->recordFeatureUsage('social_profiles');
```

The `recordFeatureUsage` method accepts 3 parameters: the first one is the feature's tag, the second one is the quantity of uses to add (default is `1`), and the third one indicates if the addition should be incremental (default behavior), when disabled the usage will be override by the quantity provided. E.g.:

```php
// Increment by 1
$user->subscription('main')->recordFeatureUsage('social_profiles', 1);

// Override with 3
$user->subscription('main')->recordFeatureUsage('social_profiles', 3, false);
```

### Reduce Feature Usage<a name="reduce-feature-usage"></a>

Reducing the feature usage is _almost_ the same as incrementing it. Here we only _substract_ a given quantity (default is `1`) to the actual usage:

```php
$user->subscription('main')->reduceFeatureUsage('social_profiles', 2);
```

### Clear the Subscription Usage data<a name="clear-subscription-usage-data"></a>

```php
$user->subscription('main')->usage()->delete();
```

### Check Subscription status<a name="check-subscription-status"></a>

For a subscription to be considered active _one of the following must be `true`_:

- Subscription has an active trial.
- Subscription `ends_at` is in the future.

```php
$user->subscribedTo($planId);
```

Alternatively you can use the following methods available in the subscription model:

```php
$user->subscription('main')->active();
$user->subscription('main')->canceled();
$user->subscription('main')->ended();
$user->subscription('main')->onTrial();
```

> Canceled subscriptions with an active trial or `ends_at` in the future are considered active.

### Renew a Subscription<a name="renew-subscription"></a>

To renew a subscription you may use the `renew` method available in the subscription model. This will set a new `ends_at` date based on the selected plan and _will clear the usage data_ of the subscription.

```php
$user->subscription('main')->renew();
```

_Canceled subscriptions with an ended period can't be renewed._

### Cancel a Subscription<a name="cancel-subscription"></a>

To cancel a subscription, simply use the `cancel` method on the user's subscription:

```php
$user->subscription('main')->cancel();
```

By default the subscription will remain active until the end of the period, you may pass `true` to end the subscription _immediately_:

```php
$user->subscription('main')->cancel(true);
```

### Scopes<a name="scopes"></a>

#### Subscription Model<a name="subscription-model"></a>

```php
// Get subscriptions by plan
$subscriptions = PlanSubscription::byPlanId($planId)->get();

// Get bookings of the given user
$user = \App\Models\User::find(1);
$bookingsOfUser = PlanSubscription::ofUser($user)->get(); 

// Get subscriptions with trial ending in 3 days
$subscriptions = PlanSubscription::findEndingTrial(3)->get();

// Get subscriptions with ended trial
$subscriptions = PlanSubscription::findEndedTrial()->get();

// Get subscriptions with period ending in 3 days
$subscriptions = PlanSubscription::findEndingPeriod(3)->get();

// Get subscriptions with ended period
$subscriptions = PlanSubscription::findEndedPeriod()->get();
```

### Models<a name="models"></a>

**Subby** uses 4 models:

```php
Bpuig\Subby\Models\Plan;
Bpuig\Subby\Models\PlanFeature;
Bpuig\Subby\Models\PlanSubscription;
Bpuig\Subby\Models\PlanSubscriptionUsage;
```

## Changelog<a name="changelog"></a>

Refer to the [Changelog](CHANGELOG.md) for a full history of the project.

## License<a name="license"></a>
Forked originally from [rinvex/laravel-subscriptions](https://github.com/rinvex/laravel-subscriptions). Thank you for creating the original!

This software is released under [The MIT License (MIT)](LICENSE).

(c) 2020 B. Puig, Some rights reserved.
