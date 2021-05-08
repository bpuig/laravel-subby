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

    /**
     * Test Attach feature
     */
    public function testAttachFeatureNotExistingInPlan()
    {
        $this->testUser->subscription('main')->features()->create([
            'tag' => 'social_koala_profiles', 'name' => 'Social profiles available for your koala', 'value' => 5, 'sort_order' => 10
        ]);

        $this->assertDatabaseHas(config('subby.tables.plan_subscription_features'), [
            'tag' => 'social_koala_profiles',
        ]);
    }

    /**
     * Test plan synchronization
     */
    public function testPlanSynchronization()
    {
        $subscription = $this->testUser->subscription('main');

        $subscription->description = 'Main description with great discount';
        $subscription->price = 4.00;

        $subscription->save();

        $this->assertTrue($this->testUser->subscription('main')->price === 4.00);

        $this->testUser->subscription('main')->syncPlan();

        $this->assertTrue($this->testUser->subscription('main')->price === 9.99);
    }
}
