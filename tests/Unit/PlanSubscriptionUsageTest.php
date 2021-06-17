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
     * Consume all of a feature and check next period without renewing monthly subscription
     */
    public function testConsumeAllFeatureAndMoveToNextUsagePeriodWithoutRenewal()
    {
        $this->testUser->subscription('main')->recordFeatureUsage('posts_per_social_profile', 30);
        $this->travelTo($this->testUser->subscription('main')->getUsageByFeatureTag('posts_per_social_profile')->valid_until->addSecond());
        $this->assertFalse($this->testUser->subscription('main')->canUseFeature('posts_per_social_profile'));
    }

    /**
     * Consume all of a feature and check next subscription period
     */
    public function testConsumeAllFeatureAndMoveToNextSubscriptionPeriod()
    {
        $this->testUser->subscription('main')->recordFeatureUsage('posts_per_social_profile', 30);
        $this->testUser->subscription('main')->renew();
        $this->travelTo($this->testUser->subscription('main')->starts_at->addSecond());
        $this->assertTrue($this->testUser->subscription('main')->canUseFeature('posts_per_social_profile'));
    }

    /**
     * Consume all of a feature and check next period
     */
    public function testRenewToNextPeriodAndUseFeature()
    {
        $this->testUser->subscription('main')->renew();
        $usage = $this->testUser->subscription('main')->recordFeatureUsage('posts_per_social_profile', 1);
        $this->assertTrue($usage->used === 1);
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
