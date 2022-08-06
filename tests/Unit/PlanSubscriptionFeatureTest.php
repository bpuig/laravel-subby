<?php


namespace Bpuig\Subby\Tests\Unit;


use Bpuig\Subby\Models\PlanSubscriptionFeature;
use Bpuig\Subby\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PlanSubscriptionFeatureTest extends TestCase
{
    use RefreshDatabase;


    /**
     * Test sync one subscription feature
     */
    public function testSyncOneSubscriptionFeature()
    {
        $feature = PlanSubscriptionFeature::first();
        $feature->value = 9999999;
        $feature->save();

        $feature->syncPlanSubscription();

        $this->assertTrue($feature->value === $feature->subscription->plan->getFeatureByTag($feature->tag)->value);
    }

    /**
     * Consume all of a feature and check if it can be used
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
    public function testRenewToNextPeriodAndThenUseFeature()
    {
        $this->testUser->subscription('main')->renew();
        $usage = $this->testUser->subscription('main')->recordFeatureUsage('social_cat_profiles', 1);
        $this->assertTrue($usage->used === 1);
    }

    /**
     * Cancel subscription immediately and check for usage
     */
    public function testImmediateCancelSubscriptionAndThenUseFeature()
    {
        $this->testUser->subscription('main')->cancel(true);
        $this->assertFalse($this->testUser->subscription('main')->canUseFeature('social_cat_profiles'));
    }

    /**
     * Cancel subscription and check for usage
     */
    public function testCancelSubscriptionAndThenUseFeature()
    {
        $this->testUser->subscription('main')->cancel();
        $this->assertTrue($this->testUser->subscription('main')->canUseFeature('social_cat_profiles'));
    }

    /**
     * Cancel subscription and check for usage next period
     */
    public function testCancelSubscriptionThenMoveToNextPeriodAndThenUseFeature()
    {
        $this->testUser->subscription('main')->cancel();
        // Travel 1 second after end of subscription
        $this->travelTo($this->testUser->subscription('main')->ends_at->addSecond());
        $this->assertFalse($this->testUser->subscription('main')->canUseFeature('social_cat_profiles'));
    }

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
        $this->expectExceptionMessage('UNIQUE constraint failed');
        $this->testUser->subscription('main')->features()->create(['tag' => 'social_profiles', 'name' => 'Social profiles', 'value' => 10, 'sort_order' => 10]);
    }

    /**
     * Test deletion of features not in synced plan
     */
    public function testDeleteAttachedFeatures()
    {
        $this->testUser->subscription('main')->features()->create(['tag' => 'attached_feature', 'name' => 'An attached feature that does not exist in plan.', 'value' => 10, 'sort_order' => 10]);
        $this->testUser->subscription('main')->syncPlanFeatures($this->testPlanBasic);
        $this->assertEmpty($this->testUser->subscription('main')->features()->withoutPlan()->get());
    }
}
