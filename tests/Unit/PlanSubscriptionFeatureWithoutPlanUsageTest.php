<?php


namespace Bpuig\Subby\Tests\Unit;


use Bpuig\Subby\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;

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
     * Consume all of a feature and renew
     */
    public function testConsumeAllFeatureAndRenew()
    {
        $this->testUser->subscription('main')->recordFeatureUsage('social_cat_profiles', 10);
        $this->testUser->subscription('main')->renew();
        $this->assertFalse($this->testUser->subscription('main')->canUseFeature('social_cat_profiles'));
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

    /**
     * Cancel subscription without fallback plan
     */
    public function testCancelSubscriptionWithoutFallbackPlan()
    {
        Config::set('subby.fallback_plan_tag', null);
        $this->testUser->subscription('main')->cancel();
        $this->assertNotNull($this->testUser->subscription('main')->canceled_at);
    }

    /**
     * Cancel subscription with fallback plan
     */
    public function testCancelSubscriptionWithFallbackPlan()
    {
        Config::set('subby.fallback_plan_tag', 'pro');
        $this->testUser->subscription('main')->cancel();
        $this->assertTrue($this->testUser->subscription('main')->plan->tag === 'pro');
    }

    /**
     * Cancel subscription with a fallback plan that does not exist
     */
    public function testCancelSubscriptionWithInexistentFallbackPlan()
    {
        Config::set('subby.fallback_plan_tag', 'super-pro');
        $this->expectException('UnexpectedValueException');
        $this->testUser->subscription('main')->cancel();
    }

    /**
     * Cancel subscription with fallback plan and ignore it
     */
    public function testCancelSubscriptionAndIgnoreFallbackPlan()
    {
        Config::set('subby.fallback_plan_tag', 'pro');
        $this->testUser->subscription('main')->cancel(false, true);
        $this->assertTrue($this->testUser->subscription('main')->plan->tag === 'basic');
    }
}
