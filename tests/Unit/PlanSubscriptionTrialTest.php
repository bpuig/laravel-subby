<?php


namespace Bpuig\Subby\Tests\Unit;


use Bpuig\Subby\Models\Plan;
use Bpuig\Subby\Tests\Database\Factories\UserFactory;
use Bpuig\Subby\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PlanSubscriptionTrialTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test subscription without trial
     */
    public function testPlanSubscriptionWithoutTrial()
    {
        $plan = Plan::create([
            'tag' => 'test-no-trial',
            'name' => 'New Plan without trial',
            'trial_period' => 0,
            'trial_interval' => 'hour',
            'grace_period' => 0,
            'grace_interval' => 'hour',
            'invoice_period' => 1,
            'invoice_interval' => 'month',
            'price' => 10,
            'tier' => 1,
            'currency' => 'EUR'
        ]);

        $user = UserFactory::new()->create();
        $user->newSubscription('main', $plan, 'Main subscription', 'Customer main subscription');

        // Subscription is not on trial and trial has ended because it never had one
        $this->assertFalse($user->subscription('main')->isOnTrial());
        $this->assertTrue($user->subscription('main')->hasEndedTrial());

        // Subscription is active, since when it does not have trial a subscription is directly made
        $this->assertTrue($user->subscription('main')->isActive());
        $this->assertFalse($user->subscription('main')->hasEnded());

        // Go to subscription end
        $this->travelTo($user->subscription('main')->ends_at->addSecond());

        // Subscription has ended
        $this->assertFalse($user->subscription('main')->isActive());
        $this->assertTrue($user->subscription('main')->hasEnded());
    }

    /**
     * Test subscription with trial
     */
    public function testPlanSubscriptionWithTrial()
    {
        $plan = Plan::create([
            'tag' => 'test-with-trial',
            'name' => 'New Plan with trial',
            'grace_period' => 0,
            'grace_interval' => 'hour',
            'trial_period' => 7,
            'trial_interval' => 'day',
            'invoice_period' => 1,
            'invoice_interval' => 'month',
            'price' => 10,
            'tier' => 1,
            'currency' => 'EUR'
        ]);

        $user = UserFactory::new()->create();
        $user->newSubscription('main', $plan, 'Main subscription', 'Customer main subscription');

        // Subscription is on trial and trial, hence it has not ended and its active
        $this->assertTrue($user->subscription('main')->isOnTrial());
        $this->assertTrue($user->subscription('main')->isActive());
        $this->assertFalse($user->subscription('main')->hasEndedTrial());
        $this->assertFalse($user->subscription('main')->hasEnded());

        // Go to trial end
        $this->travelTo($user->subscription('main')->trial_ends_at->addSecond());

        // Subscription is no more on trial
        $this->assertFalse($user->subscription('main')->isOnTrial());
        $this->assertTrue($user->subscription('main')->hasEndedTrial());

        // And since we did not renew, subscription is no longer active
        $this->assertFalse($user->subscription('main')->isActive());
        $this->assertTrue($user->subscription('main')->hasEnded());
    }
}
