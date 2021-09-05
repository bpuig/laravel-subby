# Upgrade v4 to v5

## Composer

In your composer version, require v4.

```json
"bpuig/laravel-subby": "^5.0",
```

## Config
New lines in config:
```php 
'fallback_plan_tag' => null,
```

## Migrations

Publish v5 migrations

```shell
php artisan vendor:publish --tag=subby.migrations.v5.0.0
php artisan migrate
```

## Breaking changes

### `getDaysUntilTrialEnds` method in subscription

Is now named `getTrialPeriodRemainingUsageIn('day')` and accepts new parameters.

### `getTotalDurationInDays` method in subscription

Is now named `getSubscriptionTotalDurationIn('day')` and accepts new parameters.

### `getDaysUntilEnds` method in subscription

Is now named `getSubscriptionPeriodRemainingUsageIn('day')` and accepts new parameters.

### `getRemainingPeriodProportion` method in subscription

Is now named `getRemainingSubscriptionPeriodProportion()` and accepts new parameters.

### `getRemainingPriceProrate` method in subscription

Is now named `getSubscriptionRemainingUsagePriceProrate()` and accepts new parameters.

### `setNewPeriod` method in subscription
`setNewPeriod` has been removed.
