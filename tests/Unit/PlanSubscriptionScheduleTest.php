<?php


namespace Bpuig\Subby\Tests\Unit;


use Bpuig\Subby\Jobs\SubscriptionSchedulePaymentJob;
use Bpuig\Subby\Models\PlanSubscriptionSchedule;
use Bpuig\Subby\Services\PendingPaymentCollector;
use Bpuig\Subby\Tests\TestCase;
use Carbon\Carbon;


class PlanSubscriptionScheduleTest extends TestCase
{
    /**
     * Test Create a schedule
     */
    public function testScheduleCreation()
    {
        $date = Carbon::now()->add(5, 'day');
        $this->testUser->subscription('main')->toPlan($this->testPlanPro)->onDate($date)->setSchedule();

        $schedule = PlanSubscriptionSchedule::where('plan_id', $this->testPlanPro->id)
            ->where('subscription_id', $this->testUser->subscription('main')->id)
            ->where('scheduled_at', $date->format('Y-m-d H:i:s'))
            ->first();

        $this->assertNotNull($schedule);
    }

    /**
     * Test Create a schedule without date
     */
    public function testScheduleCreationWithoutDate()
    {
        $this->expectExceptionMessage('Scheduled date is empty.');
        $this->testUser->subscription('main')->toPlan($this->testPlanPro)->setSchedule();
    }

    /**
     * Test Create a schedule without plan
     */
    public function testScheduleCreationWithoutPlan()
    {
        $date = Carbon::now()->add(5, 'day');
        $this->expectExceptionMessage('Scheduled plan is empty.');
        $this->testUser->subscription('main')->onDate($date)->setSchedule();
    }

    /**
     * Test Create a schedule with wrong plan
     */
    public function testScheduleCreationWithWrongPlan()
    {
        $date = Carbon::now()->add(5, 'day');
        $this->expectExceptionMessage('Plan is not a valid Eloquent Plan Model instance.');
        $this->testUser->subscription('main')->toPlan('test')->onDate($date)->setSchedule();
    }

    /**
     * Test Create schedule in the past
     */
    public function testPastScheduleCreation()
    {
        $date = Carbon::now()->sub(1, 'day');
        $this->expectExceptionMessage('Schedule cannot be set in the past.');
        $this->testUser->subscription('main')->toPlan($this->testPlanPro)->onDate($date)->setSchedule();
    }

    /**
     * Test change the plan and the same plan change exists in the database scheduled before this change
     */
    public function testSamePlanChangeAsPreviousPlan()
    {
        // Prepare previous plan data
        $date = Carbon::now()->add(1, 'day');
        $this->testUser->subscription('main')->toPlan($this->testPlanPro)->onDate($date)->setSchedule();

        // New plan data
        $date->add(1, 'day');
        $this->expectExceptionMessage('Previous plan change is to the same plan.');
        $this->testUser->subscription('main')->toPlan($this->testPlanPro)->onDate($date)->setSchedule();
    }

    /**
     * Test change the plan and the same plan change exists in the database scheduled after this change
     */
    public function testSamePlanChangeAsNextPlan()
    {
        // Prepare next plan data
        $date = Carbon::now()->add(2, 'day');
        $this->testUser->subscription('main')->toPlan($this->testPlanPro)->onDate($date)->setSchedule();

        // New plan data
        $date->sub(1, 'day');
        $this->expectExceptionMessage('Next plan change is to the same plan.');
        $this->testUser->subscription('main')->toPlan($this->testPlanPro)->onDate($date)->setSchedule();
    }

    /**
     * Test an acceptable concatenation of different plans
     */
    public function testMultiplePlanScheduleCreation()
    {
        $anExceptionWasThrown = false;

        try {
            $date = Carbon::now()->add(2, 'day');
            $this->testUser->subscription('main')->toPlan($this->testPlanPro)->onDate($date)->setSchedule();

            $date->add(2, 'day');
            $this->testUser->subscription('main')->toPlan($this->testPlanBasic)->onDate($date)->setSchedule();

            $date->add(2, 'day');
            $this->testUser->subscription('main')->toPlan($this->testPlanPro)->onDate($date)->setSchedule();
        } catch (\Exception $e) {
            $anExceptionWasThrown = true;
        }

        $this->assertFalse($anExceptionWasThrown);
    }

    /**
     * Test a successful payment schedule
     * @throws \Exception
     */
    public function testSuccessfulJob()
    {
        $date = Carbon::now()->add(10, 'day');
        $subscription = $this->testUser->subscription('main');
        $subscription->payment_method = 'success';
        $subscription->save();

        $subscription->toPlan($this->testPlanPro)->onDate($date)->setSchedule();

        $this->travelTo($date->add(5, 'second'));

        $pendingPaymentCollector = new PendingPaymentCollector();
        $pendingPayments = $pendingPaymentCollector->collectScheduledPayments();

        $job = (new SubscriptionSchedulePaymentJob($pendingPayments[0]['collectable_id']));
        dispatch_sync($job);

        $planSubscriptionSchedule = app(config('subby.models.plan_subscription_schedule'))::find($pendingPayments[0]['collectable_id']);


        $this->assertNull($planSubscriptionSchedule->failed_at);
        $this->assertNotNull($planSubscriptionSchedule->succeeded_at);
        $this->assertTrue($this->testUser->isSubscribedTo($this->testPlanPro->id));
    }

    /**
     * Test a failed payment schedule
     * @throws \Exception
     */
    public function testFailedJob()
    {
        $date = Carbon::now()->add(10, 'day');
        $subscription = $this->testUser->subscription('main');
        $subscription->payment_method = 'fail';
        $subscription->save();

        $subscription->toPlan($this->testPlanPro)->onDate($date)->setSchedule();

        $this->travelTo($date->add(5, 'second'));

        $pendingPaymentCollector = new PendingPaymentCollector();
        $pendingPayments = $pendingPaymentCollector->collectScheduledPayments();

        $job = (new SubscriptionSchedulePaymentJob($pendingPayments[0]['collectable_id']));
        $this->expectException('\Exception');
        dispatch_sync($job);

        $planSubscriptionSchedule = app(config('subby.models.plan_subscription_schedule'))::find($pendingPayments[0]['collectable_id']);

        $this->assertNotNull($planSubscriptionSchedule->failed_at);
        $this->assertNull($planSubscriptionSchedule->succeeded_at);
        $this->assertFalse($this->testUser->isSubscribedTo($this->testPlanPro->id));
    }
}
