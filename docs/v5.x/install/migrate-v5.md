# Upgrade v4 to v5

## Composer

In your composer version, require v4.

```json
"bpuig/laravel-subby": "^5.0",
```

## Migrations

Publish v5 migrations

```shell
php artisan vendor:publish --tag=subby.migrations.v5.0.0
php artisan migrate
```
