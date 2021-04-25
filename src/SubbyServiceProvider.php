<?php

declare(strict_types=1);

namespace Bpuig\Subby;

use Illuminate\Support\ServiceProvider;

class SubbyServiceProvider extends ServiceProvider
{

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register(): void
    {

    }

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->publishConfig();
        $this->publishMigrations();
    }

    /**
     * Publish package config.
     *
     * @return void
     */
    protected function publishConfig()
    {
        $this->publishes([
            __DIR__ . '/../config/config.php' => config_path('subby.php')
        ], 'subby.config');
    }


    /**
     * Publish package migrations.
     *
     * @return void
     */
    protected function publishMigrations()
    {
        $this->publishes([
            __DIR__ . '/../database/migrations/create_plans_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_plans_table.php'),
            __DIR__ . '/../database/migrations/create_plan_features_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time() + 1) . '_create_plan_features_table.php'),
            __DIR__ . '/../database/migrations/create_plan_subscriptions_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time() + 2) . '_create_plan_subscriptions_table.php'),
            __DIR__ . '/../database/migrations/create_plan_subscription_usage_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time() + 3) . '_create_plan_subscription_usage_table.php')
        ], 'subby.migrations');

        $this->publishes([
            __DIR__ . '/../database/migrations/v3.0.0/alter_plan_subscription_change_user.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_alter_plan_subscription_change_user.php'),
            __DIR__ . '/../database/migrations/v3.0.0/alter_plans_table_default_invoice.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_alter_plans_table_default_invoice.php'),
            __DIR__ . '/../database/migrations/v3.0.0/remove_softdeletes_laravel_subby.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_remove_softdeletes_laravel_subby.php')
        ], 'subby.migrations.v3.0.0');

        $this->publishes([
            __DIR__ . '/../database/migrations/PlanSubscriptionSchedule/create_plan_subscription_schedules_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_plan_subscription_schedules_table.php')
        ], 'subby.migrations.plan-subscription-schedule');
    }

}
