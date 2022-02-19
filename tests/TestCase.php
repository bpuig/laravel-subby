<?php

namespace Bpuig\Subby\Tests;

use Bpuig\Subby\Models\Plan;
use Bpuig\Subby\Models\PlanFeature;
use Bpuig\Subby\SubbyServiceProvider;
use Bpuig\Subby\Tests\Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TestCase extends \Orchestra\Testbench\TestCase
{
    use RefreshDatabase;

    protected $testUser;
    protected $testPlanBasic;
    protected $testPlanPro;

    public function setUp(): void
    {
        parent::setUp();

        $this->setupDefaultTestData();
    }


    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('subby', [
            'main_subscription_tag' => 'main',
            'fallback_plan_tag' => null,
            // Database Tables
            'tables' => [
                'plans' => 'plans',
                'plan_combinations' => 'plan_combinations',
                'plan_features' => 'plan_features',
                'plan_subscriptions' => 'plan_subscriptions',
                'plan_subscription_features' => 'plan_subscription_features',
                'plan_subscription_schedules' => 'plan_subscription_schedules',
                'plan_subscription_usage' => 'plan_subscription_usage'
            ],
            // Models
            'models' => [
                'plan' => \Bpuig\Subby\Models\Plan::class,
                'plan_combination' => \Bpuig\Subby\Models\PlanCombination::class,
                'plan_feature' => \Bpuig\Subby\Models\PlanFeature::class,
                'plan_subscription' => \Bpuig\Subby\Models\PlanSubscription::class,
                'plan_subscription_feature' => \Bpuig\Subby\Models\PlanSubscriptionFeature::class,
                'plan_subscription_schedule' => \Bpuig\Subby\Models\PlanSubscriptionSchedule::class,
                'plan_subscription_usage' => \Bpuig\Subby\Models\PlanSubscriptionUsage::class,
            ],
            'services' => [
                'payment_methods' => [
                    'success' => \Bpuig\Subby\Tests\Services\PaymentMethods\SucceededPaymentMethod::class,
                    'fail' => \Bpuig\Subby\Tests\Services\PaymentMethods\FailedPaymentMethod::class
                ]
            ]
        ]);

        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    /**
     * Get package providers.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            SubbyServiceProvider::class,
        ];
    }

    /**
     * Define database migrations.
     *
     * @return void
     */
    protected function defineDatabaseMigrations()
    {
        $this->loadLaravelMigrations(['--database' => 'testbench']);

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    /**
     * Set up supervised data for testing
     */
    protected function setupDefaultTestData()
    {
        // Create test user
        $this->testUser = UserFactory::new()->create(['id' => 1, 'email' => 'test@user.com']);

        // Create a Basic plan
        $this->testPlanBasic = Plan::create([
            'tag' => 'basic',
            'name' => 'Basic Plan',
            'description' => 'Basic plan description',
            'is_active' => true,
            'price' => 9.99,
            'currency' => 'EUR',
            'invoice_period' => 1,
            'invoice_interval' => 'month',
            'tier' => 1
        ]);

        $this->testPlanBasic->refresh();

        // Add some features to the Basic Plan
        $this->testPlanBasic->features()->saveMany([
            new PlanFeature(['tag' => 'social_profiles', 'name' => 'Social profiles available', 'value' => 3, 'sort_order' => 1]),
            new PlanFeature(['tag' => 'posts_per_social_profile', 'name' => 'Scheduled posts per profile', 'value' => 30, 'sort_order' => 10, 'resettable_period' => 1, 'resettable_interval' => 'month']),
            new PlanFeature(['tag' => 'analytics', 'name' => 'Analytics', 'value' => false, 'sort_order' => 15])
        ]);

        $this->testPlanBasic->combinations()->create([
            'tag' => 'test',
            'country' => 'ESP',
            'currency' => 'EUR',
            'price' => 99.99,
            'invoice_period' => 1,
            'invoice_interval' => 'year'
        ]);

        // Create a Pro plan
        $this->testPlanPro = Plan::create([
            'tag' => 'pro',
            'name' => 'Pro Plan',
            'description' => 'Pro plan description',
            'is_active' => true,
            'price' => 19.99,
            'currency' => 'EUR',
            'tier' => 2
        ]);

        $this->testPlanPro->refresh();

        // Add some features to the Basic Plan
        $this->testPlanPro->features()->saveMany([
            new PlanFeature(['tag' => 'social_profiles', 'name' => 'Social profiles available', 'value' => 5, 'sort_order' => 1]),
            new PlanFeature(['tag' => 'posts_per_social_profile', 'name' => 'Scheduled posts per profile', 'value' => 60, 'sort_order' => 10, 'resettable_period' => 1, 'resettable_interval' => 'month']),
            new PlanFeature(['tag' => 'analytics', 'name' => 'Analytics', 'value' => true, 'sort_order' => 15])
        ]);

        // Subscribe test user to plan
        $this->testUser->newSubscription('main', $this->testPlanBasic);

        $this->testUser->subscription('main')->features()->create(['tag' => 'social_cat_profiles', 'name' => 'Social profiles available for your cat', 'value' => 10, 'sort_order' => 10]);
    }
}
