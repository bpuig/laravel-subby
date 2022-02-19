# Plan Subscription Schedule

[[toc]]

Want to change a subscription but not right now? Schedule it at the end of the period? With this model you can
schedule your subscription plan changes.

## What it does

- Plan Subscription is scheduled to change to another plan or plan combination at a certain date in the future.
    * In this schedule you specify date and which service will be executed before the change.
    * You can also set a timeout and tries for the job.
- A job is added in your app schedule
- When the time comes, job batches all pending schedules and dispatches it.
    * Job will execute your defined service before plan change, if it succeeds, change will be done. If it fails,
      schedule will be flagged as failed.

## Usage

### Create schedule <Badge text="updated in v6.0" type="warning"/>
::: danger Breaking change in v6.0
Method `usingService` is abandoned to use subscription's payment method.
:::

You can schedule a change in your user subscription like this:

```php
$date = Carbon::now()->add(15, 'day');

$proPlan = Plan::findByTag('pro-plan');

$user->subscription('main')->toPlan($proPlan)->onDate($date)->setSchedule();
```

## Scopes

These are the scopes you can apply to your `PlanSubscriptionSchedule` model.

`unprocessed()` returns all unprocessed schedules (not having success or failure) in the past and in the future.

`pending()` returns a list of schedules that have not been processed and are due to be processed. To define an ending
date, use `Carbon` date as parameter to show pending until specified date. Default returns pending until now.

## Latest and first schedule to date
With this functions you can retrieve your latest schedule to date, or your next upcoming schedule. Both return `null` if
there are no schedules. 

This is useful in case you want to always edit the latest schedule and not create new ones.
```php 
$user->subscription('main')->getLatestSchedule(); // Get latest schedule before date (now() or parameter with date)
$user->subscription('main')->getFirstSchedule(); // Get first schedule after date (now() or parameter with date)
```

## Service <Badge text="updated in v6.0" type="warning"/>
::: danger Breaking change in v6.0
There are no longer multiple services to process the schedule. There is only one and it uses payments set via config.
:::

By default, the config file includes a service for processing your plan change. This service it's a good
starting point to see how it works. The service is a template service that will use payment method and do the plan change.

### How to make a service?

In the `ScheduleService` you can see the minimum requirements of a service.

Your own service has to implement interface `PlanSubscriptionScheduleService`. `__construct` accepts one parameter, the
`PlanSubscriptionSchedule` Eloquent object of the subscription schedule.

In this file you can see how it works. A change is considered failed when an exception is raised. Any exception will stop
the process and flag it as failed. If no exceptions are raised, it means payment has been successful and change can be done.

```php
<?php


declare(strict_types=1);

namespace Bpuig\Subby\Services;

use Bpuig\Subby\Contracts\PlanSubscriptionScheduleService;
use Bpuig\Subby\Models\PlanSubscriptionSchedule;
use function app;

class ScheduleService implements PlanSubscriptionScheduleService
{
    private $planSubscriptionSchedule;

    /**
     * ScheduleService constructor.
     * Save current Plan Subscription Schedule
     * @param PlanSubscriptionSchedule $planSubscriptionSchedule
     */
    public function __construct(PlanSubscriptionSchedule $planSubscriptionSchedule)
    {
        $this->planSubscriptionSchedule = $planSubscriptionSchedule;
    }

    /**
     * Execute the strategy
     * Try charging via default payment method and then change plan
     * @throws \Exception
     */
    public function execute()
    {
        try {
            $payment = app()->make(config('subby.services.payment_methods.' . $this->planSubscriptionSchedule->subscription->payment_method));
            $payment->charge();
        } catch (\Exception $exception) {
            $this->planSubscriptionSchedule->fail();
            throw new \Exception($exception->getMessage(), $exception->getCode());
        }

        $this->planSubscriptionSchedule->changeSubscriptionPlan(true, true);
    }
}

```

### Service options

The defined options for the job that will call the service will be defined in constants the service file. By default, 
PlanSubscriptionSchedule contract has this settings.
```php
const TRIES=3; // Number of tries job will be attempted
const TIMEOUT=120; // Timeout for the job that will launch the service.
```

## Dispatch the schedule job

See [Subscription payment queuer job](../payments/jobs/subscription-payment-queuer-job.md)
