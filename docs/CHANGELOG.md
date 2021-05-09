# Changelog

All notable changes to `laravel-subby` will be documented in this file.

## 4.0.0

### New

- Added Plan Subscription Features, this is a snapshot of the features parent plan han at the moment of creation.

### Breaking Changes

#### Plans

- Removed `hasGrace()` method from plan and it's database related columns.
- Removed columns from database that had no logic implemented:
    - `prorate_day`, `prorate_period`, `prorate_extend_due`, `active_subscribers_limit`, `grace_period`
      , `grace_interval`, `timezone`

#### Plan subscription

- Dettached plan subscriptions from plans, now they are their own replica of the plan. This makes the legal part of the
  subscription easier since when someone subscribes to a plan, and then you change the plan, it will affect existing
  contracts and in some places, changing conditions unilaterally can put you in trouble. Plan is referenced only for
  reference features.
    - Now plan subscription clones plan columns `price`, `currency`, `invoice_period`, `invoice_interval` and `tier`.
      They will stay like that even when you change parent plan prices.
- Add method `isFree()`.
- `newSubscription()` method fourth parameter is `$description` instead of `$startDate`. By default, it takes plan's
  description.

#### Plan subscription usage

- Removed columns from database that had no logic implemented:
    - `timezone`

#### Plan subscription Schedule

- Now schedules are separated in their own extension, which allows use of Laravel 7.

## 3.0.2

## Fix

- Update docs broken link
- Remove art from repo, use external url

## 3.0.1

### Fix

- Removed whitespace at the end of migration filename

## 3.0.0

### Breaking Changes

- Rename `User` to `Subscriber` for a more generic use. Was not thought that a subscriber could be a customer, team,
  account... etc
- `ofUser()` method in `PlanSubscription` now is `ofSubscriber()`

Changed method names to avoid confusion with possible scopes.

```php
$user->subscribedTo($planId);
$user->subscription('main')->active();
$user->subscription('main')->canceled();
$user->subscription('main')->ended();
$user->subscription('main')->onTrial();
```

Now are:

```php
$user->isSubscribedTo($planId);
$user->subscription('main')->isActive();
$user->subscription('main')->isCanceled();
$user->subscription('main')->hasEnded();
$user->subscription('main')->isOnTrial();
```

### Changes

- Default value in *plans* table for `invoice_period` is now 1.
- Removed softDeletes
