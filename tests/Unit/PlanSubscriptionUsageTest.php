<?php


namespace Bpuig\Subby\Tests\Unit;


use Bpuig\Subby\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PlanSubscriptionUsageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Consume all of a feature and check if can use
     */
    public function testConsumeAllFeature()
    {
        $this->testUser->subscription('main')->recordFeatureUsage('social_profiles', 3);
        $this->assertFalse($this->testUser->subscription('main')->canUseFeature('social_profiles'));
    }

    /**
     * Consume all of a feature and check next period
     */
    public function testConsumeAllFeatureAndRenewToNextPeriod()
    {
        $this->testUser->subscription('main')->recordFeatureUsage('posts_per_social_profile', 30);
        $this->testUser->subscription('main')->renew();
        $this->assertTrue($this->testUser->subscription('main')->canUseFeature('posts_per_social_profile'));
    }

    /**
     * Cancel subscription immediately and check for usage
     */
    public function testImmediateCancelSubscriptionAndUseIt()
    {
        $this->testUser->subscription('main')->cancel(true);
        $this->assertFalse($this->testUser->subscription('main')->canUseFeature('posts_per_social_profile'));
    }

    /**
     * Cancel subscription and check for usage
     */
    public function testCancelSubscriptionAndUseIt()
    {
        $this->testUser->subscription('main')->cancel();
        $this->assertTrue($this->testUser->subscription('main')->canUseFeature('posts_per_social_profile'));
    }

    /**
     * Cancel subscription and check for usage next period
     */
    public function testCancelSubscriptionMoveToNextPeriodAndUseIt()
    {
        $this->testUser->subscription('main')->cancel();
        // Travel 1 second after end of subscription
        $this->travelTo($this->testUser->subscription('main')->ends_at->addSecond());
        $this->assertFalse($this->testUser->subscription('main')->canUseFeature('posts_per_social_profile'));
    }
}
