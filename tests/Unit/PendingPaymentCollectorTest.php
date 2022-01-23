<?php


namespace Bpuig\Subby\Tests\Unit;


use Bpuig\Subby\Services\PendingPaymentCollector;
use Bpuig\Subby\Tests\Database\Factories\UserFactory;
use Bpuig\Subby\Tests\TestCase;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PendingPaymentCollectorTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test pending payment collector
     */
    public function testPendingPaymentCollector()
    {
        // Cancel main test data
        $this->testUser->subscription('main')->cancel(true);

        // Generate 3 users with subscription
        $users = UserFactory::new()->count(3)->create();
        foreach ($users as $user) {
            $user->newSubscription('main', $this->testPlanBasic);
        }

        $this->travelTo($users[0]->subscription('main')->ends_at->addSecond());

        $collector = new PendingPaymentCollector();
        $pending = $collector->collectPayments();

        $this->assertCount(3, $pending);
    }

    /**
     * Test pending payment collector schedule
     */
    public function testScheduledPendingPaymentCollector()
    {
        // Generate 3 users with subscription
        $users = UserFactory::new()->count(3)->create();
        foreach ($users as $user) {
            $user->newSubscription('main', $this->testPlanBasic);
        }

        // Schedule first user to end_date
        $date = $users[0]->subscription('main')->ends_at;
        $users[0]->subscription('main')->toPlan($this->testPlanPro)->onDate($date)->setSchedule();

        // Schedule second user to another date in the future
        $newDate = Carbon::parse($date)->addHour();
        $users[1]->subscription('main')->toPlan($this->testPlanPro)->onDate($newDate)->setSchedule();

        $this->travelTo($date->addSecond());

        $collector = new PendingPaymentCollector();
        $pending = $collector->collectScheduledPayments();

        // Assert that of the 2 schedules, only 1 is set to schedule at current date (ends_at + 1 second)
        $this->assertCount(1, $pending);
    }

    /**
     * Test all pending payment collector subscriptions are unique
     */
    public function testAllPendingPaymentCollectorSubscriptionsAreUnique()
    {
        // Cancel main test data
        $this->testUser->subscription('main')->cancel(true);

        // Generate user with subscription
        $user = UserFactory::new()->create();
        $user->newSubscription('main', $this->testPlanBasic);

        // Schedule to end_date
        $date = $user->subscription('main')->ends_at;
        $user->subscription('main')->toPlan($this->testPlanPro)->onDate($date)->setSchedule();
        $user->subscription('main')->toPlan($this->testPlanBasic)->onDate($date->addSecond())->setSchedule();

        $this->travelTo($date->addSecond());

        $collector = new PendingPaymentCollector();
        $pending = $collector->collectAllPayments();

        // Assert that of the 2 set schedules and regular renewal, only 1 will be processed
        $this->assertCount(1, $pending);
        $this->assertEquals($user->subscription('main')->ends_at, $pending[0]['date']);
    }
}
