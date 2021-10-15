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

# Upgrade from v4.x to v5.x

This package need to be upgraded version by version to apply database changes. See [migrate v5](migrate-v5.md) for breaking
changes.


# Attach Subscriptions to model

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
