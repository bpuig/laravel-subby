# Plan Subscription Feature Model

This model relates to which features has a Subscription.

## How does it work?

A [subscription](/models/plan-subscription-model.md) has features that can be used. This features are either assigned
and inherited of plan when user is subscribed to a plan or manually assigned without relation to a plan.

### Example

A plan has `social_profiles` feature. When subscriber is subscribed to that plan, the subscription can now
use `social_profiles`.

```php
$user->newSubscription('main', $plan, 'Main subscription', 'Customer main subscription');

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

Now user can also make use of the `pictures_per_social_profile` feature and it will be reset monthly.

### Attach existing plan feature

If subscriber has inherited a feature from a plan, there cannot be two features with the same tag attached to
subscriber. But, since subscription features do not depend anymore from plan features, you can override said feature.

```php
// Modify feature limit for subscriber
$user->subscription('main')->features()->where('tag', 'pictures_per_social_profile')
    ->update([     
       'value' => 60,
    ]);
```

Just like that, our user will be always capable of using 60 pictures, no matter what the plan feature limit is.
