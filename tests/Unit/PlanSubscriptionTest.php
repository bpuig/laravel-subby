<?php


namespace Bpuig\Subby\Tests\Unit;


use Bpuig\Subby\Models\Plan;
use Bpuig\Subby\Tests\TestCase;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PlanSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test Subscription to already existing tag
     */
    public function testUnableToCreatePlanSubscriptionWithExistingTag()
    {
        $this->expectException(QueryException::class);
        $this->expectExceptionMessage('UNIQUE constraint failed: plan_subscriptions.tag, plan_subscriptions.subscriber_id, plan_subscriptions.subscriber_type');
        $this->testUser->newSubscription('main', $this->testPlanBasic, 'Test');
    }

    /**
     * Test Subscription to a deleted subscription
     */
    public function testAbleToCreatePlanSubscriptionWithExistingTagAndDeletedPrevious()
    {
        $this->testUser->subscription('main')->delete();
        $anExceptionWasThrown = false;

        try {
            $this->testUser->newSubscription('main', $this->testPlanBasic, 'Test');
        } catch (QueryException $e) {
            $anExceptionWasThrown = true;
        }

        $this->assertFalse($anExceptionWasThrown);
    }
}
