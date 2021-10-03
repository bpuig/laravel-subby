<?php


namespace Bpuig\Subby\Tests\Unit;


use Bpuig\Subby\Models\Plan;
use Bpuig\Subby\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PlanGraceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test Plan creation with grace
     */
    public function testPlanHasGrace() : Plan
    {
       $plan = Plan::create([
            'tag' => 'basic-grace',
            'name' => 'New Basic Plan with Grace',
            'description' => 'This plan has grace.',
            'currency' => 'EUR',
            'grace_interval' => 'day',
            'grace_period' => 4
        ]);

       $this->assertTrue($plan->hasGrace());

       return $plan;
    }

    /**
     * Test Subscription with grace period
     * @depends testPlanHasGrace
     */
    public function testSubscriptionHasGrace($plan): void
    {
        $this->testUser->newSubscription('grace', $plan);

        dd($this->testUser->subscription('grace'));

        $this->assertTrue($this->testUser->subscription('grace')->hasGrace());
    }
}
