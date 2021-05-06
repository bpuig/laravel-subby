# Plan Subscription Schedule

Want to change a subscription but not right now? Schedule it at the end of the period? With this optional extra feature
you can schedule your subscription plan changes.

## Installation

Installation is simple, follow this steps:

### Previous Requirements

This extra uses Laravel 8 batch processing. If you do not use the Jobs included in the package and make your own
processing jobs you do not need batch processing tables. If you intend to use package jobs refer
to [Queues: Job batching](https://laravel.com/docs/8.x/queues#job-batching) in Laravel's official documentation.

### Configuration

#### Upgrading from 0.1.x

Versions lower than `0.2.0` do not include this part in config. If you installed this package after version `0.2.0`you
can skip this step.

In config file `subby.php` include after `models` array:

```php

// Plan schedule settings (Optional if you do not use IsScheduled trait)
'schedule' => [
    'schedules_per_subscription' => null, // Maximum number of schedules allowed for a subscription (null for no limit)
    'tables' => [
        'plan_subscription_schedules' => 'plan_subscription_schedules'
    ],
    'models' => [
        'plan_subscription_schedule' => \Bpuig\Subby\Models\PlanSubscriptionSchedule::class,
    ],
    'services' => [
        'default' => \Bpuig\Subby\Services\PlanSubscriptionSchedule\DefaultScheduleService::class
    ]
]
```

#### Upgrading from 0.2.x

Versions lower than `0.3.0` do not include `schedules_per_subscription` in config. Default behaviour is not breaking.
You can set it to `null` to continue with functionality as it was in `0.2.0`.

### Migrations

Publish specific migrations for this extra:

```shell
php artisan vendor:publish --tag=subby.migrations.plan-subscription-schedule
```

Migrate:

```shell
php artisan migrate
```

### Extend Plan Subscription Model

Since this extra is activated by a trait in your `PlanSubscription` model, you need to extend it.

```shell
php artisan make:model PlanSubscription
```

Then edit it to extend package model and add `IsScheduled` trait.

```php
<?php

namespace App\Models;

use Bpuig\Subby\Traits\PlanSubscriptionSchedule\IsScheduled;

class PlanSubscription extends \Bpuig\Subby\Models\PlanSubscription
{
    use IsScheduled;
}

```

In your `subby` config file locate and change used model with the model you extended in previous step:

```php
// Models
'models' => [
    ...
    'plan_subscription' => \App\Models\PlanSubscription::class,
    ...
]
```

That is it, now you are capable of scheduling subscription plan changes.

## What it does

- Plan Subscription is scheduled to change to another plan at a certain date in the future.
    * In this schedule you specify date and which service will be executed before the change.
    * You can also set a timeout and tries for the job.
- A job is added in your app schedule
- When the time comes, job batches all pending schedules and dispatches it.
    * Job will execute your defined service before plan change, if it succeeds, change will be done. If it fails,
      schedule will be flagged as failed.

## Usage

### Create schedule

You can schedule a change in your user subscription like this:

```php
$date = Carbon::now()->add(15, 'day');
$user->subscription('main')->toPlan($this->testPlanPro)->onDate($date)->setSchedule();
```

You can set other options like:

- `service()`: References [service](#services) name in config file.
- `timeout()`: Timeout for the job that will launch the service.
- `tries()`: Number of tries job will be attempted

```php
$date = Carbon::now()->add(15, 'day');
$user->subscription('main')->toPlan($this->testPlanPro)->onDate($date)->service('default')->tries(2)->timeout(200)->setSchedule();
```

### Schedules per subscription

As introduced in `0.3.0`, you can now set a limit of schedules per subscription in your config.

You can check if subscription has reached its limit on your subscription object
with `$subscription->reachedScheduleLimit()`.

You can also dynamically ignore the schedule limit with `ignoreLimit(true|false)` (defaults to `true`):

```php
$user->subscription('main')->toPlan($this->testPlanPro)->onDate($date)->ignoreLimit()->setSchedule();
```

## Scopes

This are the scopes you can apply to your `PlanSubscriptionSchedule` model.

`notProcessed()` returns all unprocessed schedules (not having success or failure) in the past and in the future.

### Pending

`pending()` returns a list of schedules that have not been processed and are due to be processed. To define an ending
date, use `Carbon` date as parameter to show pending until specified date. Default returns pending until now.

## Services

By default, the config file includes a `default` service for processing your plan change. This service it's a good
starting point to see how it works. The default service is an empty service that will not perform any action or stop the
plan change.

### How to make a service?

In the example `DefaultScheduleService` you can see the minimum requirements of a service.

Your own service has to implement interface `PlanSubscriptionScheduleService` and also use `IsScheduleService`
trait. `__construct` accepts one parameter, the `PlanSubscriptionSchedule` Eloquent object of the subscription schedule.

The outcome to later change plan or not is defined by `success` property. By default is `false`, so your successful
process has to set it to `true`. Any exception will stop the process.

```php
<?php


namespace Bpuig\Subby\Services\PlanSubscriptionSchedule;


use Bpuig\Subby\Contracts\PlanSubscriptionScheduleService;
use Bpuig\Subby\Traits\PlanSubscriptionSchedule\IsScheduleService;

class DefaultScheduleService implements PlanSubscriptionScheduleService
{

    use IsScheduleService;

    /**
     * DefaultScheduleService constructor.
     * Save current Plan Subscription Schedule
     * @param $planSubscriptionSchedule
     */
    public function __construct($planSubscriptionSchedule)
    {
        $this->planSubscriptionSchedule = $planSubscriptionSchedule;
    }

    /**
     * Execute the strategy
     *
     * Since this is kind of a dummy process, set success to true
     */
    public function execute()
    {
        $this->success = true;
        $this->clearUsage = true;
    }
}
```

## Jobs

To process the schedules you need to include the `SubscriptionScheduleQueuerJob` in your app schedule.

I recommend setting it without overlapping, since package allows to schedule multiple plan changes for the same
subscription. In `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
    {
        ...
        $schedule->job(new SubscriptionScheduleQueuerJob)->everyFiveMinutes()->withoutOverlapping();
    }
```

This job will make a batch of jobs for pending subscription changes.

I recommend running first this job with a date parameter and then chain your regular subscription renewal job with said
date parameter as end to avoid collision since a subscription can have same `ends_at` and `scheduled_at` dates. This
order of events would prevent renewals before schedules, since after schedule is processed `ends_at` would have changed
to next period.
