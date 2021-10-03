<?php


namespace Bpuig\Subby\Tests\Unit;


use Bpuig\Subby\Models\Plan;
use Bpuig\Subby\Tests\Database\Factories\UserFactory;
use Bpuig\Subby\Tests\Models\User;
use Bpuig\Subby\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PlanGraceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test Plan creation with grace
     */
    public function testPlanHasGrace(): Plan
    {
        $plan = Plan::create([
            'tag' => 'basic-grace',
            'name' => 'New Basic Plan with Grace',
            'description' => 'This plan has grace.',
            'price' => 6,
            'currency' => 'EUR',
            'trial_interval' => 'hour',
            'trial_period' => 0,
            'grace_interval' => 'day',
            'grace_period' => 4,
            'invoice_interval' => 'month',
            'invoice_period' => 1,
            'tier' => 1
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
        $user = UserFactory::new()->create();
        $user->newSubscription('grace', $plan);

        $this->assertTrue($user->subscription('grace')->hasGrace());
    }

    /**
     * Test Subscription is active on grace period
     * @depends testPlanHasGrace
     */
    public function testSubscriptionIsActiveOnGrace($plan): void
    {
        $user = UserFactory::new()->create();
        $user->newSubscription('grace', $plan);

        dd($user->subscription('grace')->getGraceEndDate());

        $this->travelTo($user->subscription('grace')->ends_at->addSecond());
        $this->assertTrue($user->subscription('grace')->isActive());
    }
}
