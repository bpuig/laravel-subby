<?php


namespace Bpuig\Subby\Tests\Unit;


use Bpuig\Subby\Models\Plan;
use Bpuig\Subby\Models\PlanSubscriptionSchedule;
use Bpuig\Subby\Tests\TestCase;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;


class PlanSubscriptionScheduleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test Create a schedule
     */
    public function testScheduleCreation()
    {
        $date = Carbon::now()->add(5, 'day');
        $this->testUser->subscription('main')->toPlan($this->testPlanPro)->onDate($date)->setSchedule();

        $schedule =$this->testPlanPro->schedules()
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
        $this->expectExceptionMessage('Scheduled plan/combination is empty.');
        $this->testUser->subscription('main')->onDate($date)->setSchedule();
    }

    /**
     * Test Create a schedule with wrong plan
     */
    public function testScheduleCreationWithWrongPlan()
    {
        $date = Carbon::now()->add(5, 'day');
        $this->expectExceptionMessage('Argument #1 ($planCombination) must be of type Bpuig\Subby\Models\Plan|Bpuig\Subby\Models\PlanCombination');
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
}
