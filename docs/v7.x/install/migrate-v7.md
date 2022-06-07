# Upgrade v6.x to v7.x

## Composer

In your composer version, require `v7.0` version.

```json
"bpuig/laravel-subby": "^v7.0",
```

## Config

### Changed lines

Changed `plan_combinations` to `plan_combination` in models:

```php
    // Models
    'models' => [
        ...
        'plan_combination' => \Bpuig\Subby\Models\PlanCombination::class,
    ...
    ]
```

## Migrations

Publish v7 migrations

```shell
php artisan vendor:publish --tag=subby.migrations.v7
php artisan migrate
```
