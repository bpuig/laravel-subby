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

}
