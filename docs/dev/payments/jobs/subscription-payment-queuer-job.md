# Subscription Payment Queuer <Badge text="new in v6.0" type="tip"/>
This is the job that needs to be called periodically to collect all the subscriptions (either renewals or schedules) that
have to be paid and process the payments.

## What it does
The queuer job uses the Payment Collector class. This class retrieves all pending subscription renewals and also all pending
subscription plan change schedules, then the job dispatches new jobs for each type of payment method and action needed 
(renewal or schedule).

### What happens when subscription schedule is set at renewal date?
Subscription schedule change has priority over renewal, so schedule change will be first processed. Plan
change will set a new renewal time in the future, so this renewal will be pushed into the future and not processed at
the same time schedule happens to avoid duplicates.

### How does the package avoid double charging?
Both schedule and renewal jobs use Laravel's [Preventing Job Overlaps](https://laravel.com/docs/8.x/queues#preventing-job-overlaps)
without release, so only one job per subscription can be active.

## How to schedule
Your task schedule is defined in the `app/Console/Kernel.php` file's schedule method.

```php
use \Bpuig\Subby\Jobs\SubscriptionPaymentQueuerJob;

$schedule->job(new SubscriptionPaymentQueuerJob())->everyFiveMinutes();
```
