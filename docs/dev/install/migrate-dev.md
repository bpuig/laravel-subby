# Upgrade v5.x to dev

## Composer

In your composer version, require `dev-main` version.

```json
"bpuig/laravel-subby": "dev-main",
```

## Config

### New lines in config

`'services'` now look should like this:

```php 
'services' => [
        'schedule' => \Bpuig\Subby\Services\ScheduleService::class,
        'renewal' => \Bpuig\Subby\Services\RenewalService::class,
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
