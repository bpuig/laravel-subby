# Laravel Subby

**Laravel Subby** is a flexible plans and subscription management system for Laravel. Originally forked
from [rinvex/laravel-subscriptions](https://github.com/rinvex/laravel-subscriptions).

## What it does

The way this package is made:

1. There are [plans](/models/plan-model.md) that have [features](/models/plan-feature-model.md).
2. There is an entity (morph relationship) receiving the trait of
   having [subscriptions](/models/plan-subscription-model.md). It can be an user, a team, whatever you want;
   see [Attach Subscriptions to model](/install/#attach-subscription).
3. This entity can be subscribed to one or more plans and use its features and other features not attached to a plan.
   This subscription is made like a "snapshot" of current plan details. If plan is modified in the future, subscriber's
   subscription stays as it was, price, invoicing and features are "frozen" unless manually synchronized with related
   plan.

With the [scheduling extra](/extras/plan-subscription-schedule.md), you can schedule your plan changes in the future.

## Considerations

- Payments and translations are out of scope for this package.
- You may want to extend some core models, in case you need to override the logic behind some helper methods
  like `renew()`, `cancel()` etc. E.g.: when cancelling a subscription you may want to also cancel the recurring payment
  attached.

## Changelog<a name="changelog"></a>

Refer to the [Changelog](CHANGELOG.md) for a full history of the project.

## License<a name="license"></a>

Forked originally from [rinvex/laravel-subscriptions](https://github.com/rinvex/laravel-subscriptions). Thank you for
creating the original!

This software is released under [The MIT License (MIT)](LICENSE.md).

&copy; 2020-2021 B. Puig, Some rights reserved.