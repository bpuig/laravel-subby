<?php


namespace Bpuig\Subby\Tests\Unit;


use Bpuig\Subby\Models\Plan;
use Bpuig\Subby\Tests\Database\Factories\UserFactory;
use Bpuig\Subby\Tests\TestCase;
use Carbon\Carbon;
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
        $this->assertFalse($user->subscription('main')->hasEnded());

        // Go to trial end
        $this->travelTo($user->subscription('main')->trial_ends_at->addSecond());

        // Subscription is no more on trial
        $this->assertFalse($user->subscription('main')->isOnTrial());

        // And since we did not renew, subscription is no longer active
        $this->assertFalse($user->subscription('main')->isActive());
        $this->assertTrue($user->subscription('main')->hasEnded());
    }

    /**
     * Test subscription with trial inside
     */
    public function testPlanSubscriptionRenewalWithTrialInside()
    {
        $plan = Plan::create([
            'tag' => 'test-with-trial',
            'name' => 'New Plan with trial inside',
            'trial_period' => 7,
            'trial_interval' => 'day',
            'trial_mode' => 'inside',
            'invoice_period' => 1,
            'invoice_interval' => 'month',
            'price' => 10,
            'tier' => 1,
            'currency' => 'EUR'
        ]);

        $user = UserFactory::new()->create();
        $user->newSubscription('main', $plan, 'Main subscription', 'Customer main subscription');

        // Get trial start date
        $startDate = Carbon::make($user->subscription('main')->trial_ends_at)
            ->sub($plan->trial_interval, $plan->trial_period);

        // Go to trial end minus one hour to see better the difference
        $this->travelTo($user->subscription('main')->trial_ends_at->subHour());

        $user->subscription('main')->renew();

        // Compare subscription start date with trial start date since trial is INSIDE
        $this->assertTrue($startDate->equalTo($user->subscription('main')->starts_at));

        // Calculate end
        $endDate = $startDate->add($plan->invoice_interval, $plan->invoice_period);

        // Compare subscription end to trial start plus invoicing period
        $this->assertTrue($endDate->equalTo($user->subscription('main')->ends_at));
    }

    /**
     * Test subscription with trial outside
     */
    public function testPlanSubscriptionRenewalWithTrialOutside()
    {
        $plan = Plan::create([
            'tag' => 'test-with-trial',
            'name' => 'New Plan with trial outside',
            'trial_period' => 7,
            'trial_interval' => 'day',
            'trial_mode' => 'outside',
            'invoice_period' => 1,
            'invoice_interval' => 'month',
            'price' => 10,
            'tier' => 1,
            'currency' => 'EUR'
        ]);

        $user = UserFactory::new()->create();
        $user->newSubscription('main', $plan, 'Main subscription', 'Customer main subscription');

        // Travel 2 days to make sure we are in the middle of trial and date is not confused with now
        $this->travel(2)->days();

        $user->subscription('main')->renew();

        // Compare to now (2 days after subscription)
        $this->assertEquals(Carbon::now()->format('Y-m-d H:i:s'), $user->subscription('main')->starts_at->format('Y-m-d H:i:s'));
    }
}
