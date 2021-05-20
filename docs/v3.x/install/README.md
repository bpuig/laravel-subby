# Installation

Install the package via composer:

```shell
composer require bpuig/laravel-subby
```

Publish the configuration:

```shell
php artisan vendor:publish --tag=subby.config
```

Publish migrations:

```shell
php artisan vendor:publish --tag=subby.migrations
```

Migrate:

```shell
php artisan migrate
```

## Upgrade to major

This package need to be upgraded version by version to apply database changes.

### Upgrade from v0.x to v3.x

Require in composer version 3 or greater. Publish and migrate:

```shell
php artisan vendor:publish --tag=subby.migrations.v3.0.0
php artisan migrate
```

### Where are v1 and v2?

These versions had changes that made the package unusable. Since this package is not used by many people (maybe just me)
I'll take the freedom to remove them. They did more bad than good.

### Breaking changes

Find breaking changes in changelog.

## Attach Subscriptions to model<a name="attach-subscription"></a>

**Laravel Subby** has been specially made for Eloquent. To add Subscription functionality to your User model just use
the `\Bpuig\Subby\Traits\HasSubscriptions` trait like this:

```php
namespace App\Models;

use Bpuig\Subby\Traits\HasSubscriptions;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasSubscriptions;
}
```

That's it, we only have to use that trait in our User model or any other model! Now your users may subscribe to plans.
Then you can import package's models wherever you need them or extend them in your own models.
