# Upgrade v3 to v4

## Composer

In your composer version, require v4.

```json
"bpuig/laravel-subby": "^4.0",
```

## Migrations

Publish v4 migrations

```shell
php artisan vendor:publish --tag=subby.migrations.v4.0.0
php artisan migrate
```

## Config

A new model has been introduced, add new table and model in your subby config. A new config `main_subscription_tag` has
been added.

```php
'main_subscription_tag' => 'main',
'tables' => [
        ...
        'plan_subscription_features' => 'plan_subscription_features',
        ...
    ],

    // Models
    'models' => [
        ...
        'plan_subscription_feature' => \Bpuig\Subby\Models\PlanSubscriptionFeature::class,
        ...
    ],
```
