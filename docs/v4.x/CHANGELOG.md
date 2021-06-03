# Changelog

All notable changes to `laravel-subby` will be documented in this file.

## 4.0.2

## Changes

- Subscription tag scope @boryn (#69)

## Bug Fixes

- End trial when subscription is renewed. @bpuig (#68)

## 4.0.1

## Changes

## Bug Fixes

- canUseFeature also on trial @bpuig (#65)

## Documentation

- updated plan-subscription-feature-model.md @boryn (#64)

## 4.0.0
### Plans

#### Breaking Changes

- Removed `hasGrace()` method from plan and it's database related columns.
- Removed columns from database that had no logic implemented:
    - `prorate_day`, `prorate_period`, `prorate_extend_due`, `active_subscribers_limit`, `grace_period`
      , `grace_interval`, `timezone`

### Plan features

#### Breaking Changes

- Removed `usage()` method.

### Plan subscription

#### New

- Dettached plan subscriptions from plans, now they are their own replica of the plan. This makes the legal part of the
  subscription easier since when someone subscribes to a plan, and then you change the plan, it will affect existing
  contracts and in some places, changing conditions unilaterally can put you in trouble. Plan is referenced only for
  reference features.
    - Now plan subscription clones plan columns `price`, `currency`, `invoice_period`, `invoice_interval` and `tier`.
      They will stay like that even when you change parent plan prices.
- Add method `isFree()`, `getDaysUntilEnds()`, `getDaysUntilTrialEnds()`, `getRemainingPriceProrate()`
- Add method `isAltered()` to know if features are the same as in plan.
- Add method `getDaysUntilEnds()` to get number of days until subscription ends.
- Add method `getDaysUntilTrialEnds()` to get number of days until subscription trial ends.
- Add method `getRemainingPriceProrate()` to retrieve remaining price amount that would have not been consumed.

#### Breaking Changes

- `newSubscription()` method fourth parameter is `$description` instead of `$startDate`. By default, it takes plan's
  description.

### Plan subscription usage

#### Breaking Changes

- Removed `subscription` relationship.
- Removed columns from database that had no logic implemented:
    - `timezone`
- Renamed columns to keep relationships clearer:
    - `subscription_id` to `plan_subscription_id`
    - `feature_id` to `plan_subscription_feature_id`

### Plan subscription features

#### New

- Dettached subscription features from plans, now they are their own replica of the plan features.

### Plan subscription Schedule

#### Breaking Changes

- Now schedules are separated in their own extension, which allows use of Laravel 7 and 6.
