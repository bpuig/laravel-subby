<?php


namespace Bpuig\Subby\Tests\Unit;


use Bpuig\Subby\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PlanSubscriptionFeatureWithoutPlanUsageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test use attached feature without plan relation
     */
    public function testCanUseFeatureNotExistingInPlan()
    {
        $this->assertTrue($this->testUser->subscription('main')->canUseFeature('social_cat_profiles'));
    }

    /**
     * Test use attached feature existing in current related plan
     */
    public function testCannotUseFeatureExistingInCurrentRelatedPlan()
    {
        $this->expectException('Illuminate\Database\QueryException');
        $this->expectExceptionMessage('UNIQUE constraint failed: plan_subscription_features.plan_subscription_id, plan_subscription_features.tag');
        $this->testUser->subscription('main')->features()->create(['tag' => 'social_profiles', 'name' => 'Social profiles', 'value' => 10, 'sort_order' => 10]);
    }

    /**
     * Consume all of a feature and check if can use
     */
    public function testConsumeAllFeature()
    {
        $this->testUser->subscription('main')->recordFeatureUsage('social_cat_profiles', 10);
        $this->assertFalse($this->testUser->subscription('main')->canUseFeature('social_cat_profiles'));
    }

    /**
     * Consume all of a feature and check next period
     */
    public function testConsumeAllFeatureAndRenewToNextPeriod()
    {
        $this->testUser->subscription('main')->recordFeatureUsage('social_cat_profiles', 10);
        $this->testUser->subscription('main')->renew();
        $this->assertTrue($this->testUser->subscription('main')->canUseFeature('social_cat_profiles'));
    }

    /**
     * Consume all of a feature and check next period
     */
    public function testRenewToNextPeriodAndUseFeature()
    {
        $this->testUser->subscription('main')->renew();
        $usage = $this->testUser->subscription('main')->recordFeatureUsage('social_cat_profiles', 1);
        $this->assertTrue($usage->used === 1);
    }

    /**
     * Cancel subscription immediately and check for usage
     */
    public function testImmediateCancelSubscriptionAndUseIt()
    {
        $this->testUser->subscription('main')->cancel(true);
        $this->assertFalse($this->testUser->subscription('main')->canUseFeature('social_cat_profiles'));
    }

    /**
     * Cancel subscription and check for usage
     */
    public function testCancelSubscriptionAndUseIt()
    {
        $this->testUser->subscription('main')->cancel();
        $this->assertTrue($this->testUser->subscription('main')->canUseFeature('social_cat_profiles'));
    }

    /**
     * Cancel subscription and check for usage next period
     */
    public function testCancelSubscriptionMoveToNextPeriodAndUseIt()
    {
        $this->testUser->subscription('main')->cancel();
        // Travel 1 second after end of subscription
        $this->travelTo($this->testUser->subscription('main')->ends_at->addSecond());
        $this->assertFalse($this->testUser->subscription('main')->canUseFeature('social_cat_profiles'));
    }


}
