<?php


namespace Bpuig\Subby\Tests\Unit;


use Bpuig\Subby\Tests\TestCase;
use Illuminate\Database\Eloquent\Builder;
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
        $this->expectException('Bpuig\Subby\Exceptions\PlanSubscriptionTagAlreadyExists');
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
     * Test plan change
     */
    public function testPlanChange()
    {
        $subscription = $this->testUser->subscription('main');

        $subscription->changePlan($this->testPlanPro);

        // Plan has been changed
        $this->assertTrue($this->testUser->subscription('main')->plan->is($this->testPlanPro));

        // No previous plan features still attached and related to plan
        $this->assertTrue($subscription->features()->whereHas('feature', function (Builder $query) {
                $query->where('plan_id', $this->testPlanBasic);
            })->count() === 0);

        // Current plan features
        $this->assertTrue($this->testUser->subscription('main')->features()->whereHas('feature', function (Builder $query) {
                $query->where('plan_id', $this->testPlanPro->id);
            })->count() === $this->testPlanPro->features()->count());
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

    /**
     * Test subscription is not altered
     */
    public function testSubscriptionIsNotAltered()
    {
        $this->testUser->subscription('main')->syncPlan(null, true, true);
        $this->assertFalse($this->testUser->subscription('main')->isAltered());
    }

    /**
     * Test subscription is altered
     */
    public function testSubscriptionIsAltered()
    {
        $this->testUser->subscription('main')->features()->create(['tag' => 'social_dog_profiles', 'name' => 'Social profiles available for your dog', 'value' => 2, 'sort_order' => 25]);
        $this->assertTrue($this->testUser->subscription('main')->isAltered());
    }

    /**
     * Test non existing subscription exception
     */
    public function testNonExistingSubscriptionException()
    {
        $this->expectException('Bpuig\Subby\Exceptions\PlanSubscriptionNotFound');
        $this->testUser->subscription('secondary');
    }
}
