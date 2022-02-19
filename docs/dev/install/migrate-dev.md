# Upgrade v5.x to dev

## Composer

In your composer version, require `dev-main` version.

```json
"bpuig/laravel-subby": "dev-main",
```

## Config

### New lines in config

Added `plan_combinations` under `tables` and models:

```php
    'tables' => [
        ...
        'plan_combinations' => 'plan_combinations',
        ...
    ],
    // Models
    'models' => [
        ...
        'plan_combinations' => \Bpuig\Subby\Models\PlanCombination::class,
    ...
    ]
```

Added `payment_methods`, `'services'` now look should like this:

```php 
'services' => [
    'payment_methods' => [
        'free' => \Bpuig\Subby\Services\PaymentMethods\Free::class
    ]
]
```

### Removed lines in config:

In `'services'`:

```php 
'services' => [
    'schedule' => [
        'default' => \Bpuig\Subby\Services\ScheduleService::class
    ]
]
```

## Migrations

Publish dev migrations

```shell
php artisan vendor:publish --tag=subby.migrations.dev
php artisan migrate
```

## Breaking changes

### Plan subscription schedule
* Method `usingService` is abandoned to use subscription's payment method.
* There are no longer multiple services to process the schedule. There is only one and it uses payments set via config. 
