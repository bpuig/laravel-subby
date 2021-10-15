# Plan Subscription Schedule
::: danger Breaking changes in v5.0
There are some things different to  [Laravel Subby Schedule](https://github.com/bpuig/laravel-subby-schedule). In the
rare event that you were using the package, please review the docs.
- Limits were removed
- Methods were renamed: `usingService` was `service`
- Columns `tries` and `timeout` are now constants in service
- IsScheduled trait no longer exists
:::

Want to change a subscription but not right now? Schedule it at the end of the period? With this model you can
schedule your subscription plan changes.

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
- `usingService()`: References [service](#services) name in config file.

```php
$date = Carbon::now()->add(15, 'day');
$user->subscription('main')->toPlan($this->testPlanPro)->onDate($date)->usingService('default')->setSchedule();
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

## Services

By default, the config file includes a `default` service for processing your plan change. This service it's a good
starting point to see how it works. The default service is an empty service that will not perform any action or stop the
plan change.

### How to make a service?

In the example `ScheduleService` you can see the minimum requirements of a service.

Your own service has to implement interface `PlanSubscriptionScheduleService` and also use `IsScheduleService`
trait. `__construct` accepts one parameter, the `PlanSubscriptionSchedule` Eloquent object of the subscription schedule.

The outcome to later change plan or not is defined by `success` property. By default, is `false`, so your successful
process has to set it to `true`. Any exception will stop the process.

```php
<?php


namespace Bpuig\Subby\Services;


use Bpuig\Subby\Contracts\PlanSubscriptionScheduleService;
use Bpuig\Subby\Traits\IsScheduleService;

class ScheduleService implements PlanSubscriptionScheduleService
{
    use IsScheduleService;

    /**
     * ScheduleService constructor.
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

## Jobs

To process the schedules you can include the `SubscriptionScheduleQueuerJob` in your app schedule or make your own jobs.

```php
protected function schedule(Schedule $schedule)
{
    ...
    $schedule->job(new SubscriptionScheduleQueuerJob)->everyFiveMinutes();
}
```

This job will make dispatch jobs for pending subscription changes.

I recommend running first this job with a date parameter and then chain your regular subscription renewal job with said
date parameter as end to avoid collision since a subscription can have same `ends_at` and `scheduled_at` dates. This
order of events would prevent renewals before schedules, since after schedule is processed `ends_at` would have changed
to next period.
