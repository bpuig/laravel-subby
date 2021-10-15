# Upgrade v4 to v5

## Composer

In your composer version, require v5.

```json
"bpuig/laravel-subby": "^5.0",
```

## Config
New lines in config:
```php 
'fallback_plan_tag' => null,
```

### Schedule configuration

Merge this lines into your current config.

```php
// Database Tables
'tables' => [
    'plan_subscription_schedules' => 'plan_subscription_schedules'
],
'models' => [
    'plan_subscription_schedule' => \Bpuig\Subby\Models\PlanSubscriptionSchedule::class,
],
'services' => [
    'schedule' => [
        'default' => \Bpuig\Subby\Services\ScheduleService::class
    ]
]
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

### `syncPlan` method (which also relates to `changePlan`) in subscription

`syncPlan` now does not renew the subscription
