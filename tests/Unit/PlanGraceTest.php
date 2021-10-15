<?php


namespace Bpuig\Subby\Tests\Unit;


use Bpuig\Subby\Models\Plan;
use Bpuig\Subby\Tests\Database\Factories\UserFactory;
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

        // Travel one second after subscription ends
        $this->travelTo($user->subscription('grace')->ends_at->addSecond());
        $this->assertTrue($user->subscription('grace')->isActive());
        $this->assertTrue($user->subscription('grace')->hasStartedGrace());
        $this->assertTrue($user->subscription('grace')->isInGrace());
        $this->assertFalse($user->subscription('grace')->hasEndedGrace());
    }

    /**
     * Test Subscription is not active on ending grace period
     * @depends testPlanHasGrace
     */
    public function testSubscriptionIsNotActiveOnGraceEnd($plan): void
    {
        $user = UserFactory::new()->create();
        $user->newSubscription('grace', $plan);

        $graceSubscription = $user->subscription('grace');

        // Travel one second after period ends
        $this->travelTo($graceSubscription->ends_at->add($graceSubscription->grace_period, $graceSubscription->grace_interval)->addSecond());
        $this->assertFalse($graceSubscription->isActive());
        $this->assertTrue($user->subscription('grace')->hasStartedGrace());
        $this->assertFalse($user->subscription('grace')->isInGrace());
        $this->assertTrue($user->subscription('grace')->hasEndedGrace());
    }
}
