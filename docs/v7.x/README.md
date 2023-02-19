# Laravel Subby

**Laravel Subby** is a flexible plans and subscription management system for Laravel. Originally forked
from [rinvex/laravel-subscriptions](https://github.com/rinvex/laravel-subscriptions).

## What it does

The way this package is made:

1. There are [plans](models/plan-model.md) that have [features](models/plan-feature-model.md)
   1. These plans can have multiple combinations (country, currency, periods...).
2. There is an entity (morph) receiving the
   trait `HasSubscriptions` ([subscriptions](models/plan-subscription-model.md)). It can be a user, a team, whatever you
   want; see [Attach Subscriptions to model](install/#attach-subscription).
3. This entity can have many subscriptions to one or more plans and use their features and other features not attached
   to a plan. The subscription is made as a "snapshot" of current plan details. If plan is modified in the future,
   subscriber's subscription stays as it was, price, invoicing and features are "frozen" unless manually synchronized
   with related plan.

### Processing payments <Badge text="new in v6.0" type="tip"/>
Payment services are dispatched when renewal or schedule time has come. The package provides a "free" payment service,
with no logic. You need to make your own services for your payment providers.

### Scheduling changes
With [Schedule](models/plan-subscription-schedule-model.md) you can schedule one or multiple plan changes in a future date.

## Considerations

- Translations are out of scope for this package.

## Changelog

Refer to the [Releases](https://github.com/bpuig/laravel-subby/releases) for a changelog of the project.

## License

Forked originally from [rinvex/laravel-subscriptions](https://github.com/rinvex/laravel-subscriptions). Thank you for
creating the original!

This software is released under [The MIT License (MIT)](LICENSE.md).

&copy; 2020-2023 B. Puig, Some rights reserved.
