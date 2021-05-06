# Changelog

All notable changes to `laravel-subby` will be documented in this file.

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
