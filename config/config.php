<?php

declare(strict_types=1);

return [
    'main_subscription_tag' => 'main',
    'fallback_plan_tag' => null,
    // Database Tables
    'tables' => [
        'plans' => 'plans',
        'plan_features' => 'plan_features',
        'plan_subscriptions' => 'plan_subscriptions',
        'plan_subscription_features' => 'plan_subscription_features',
        'plan_subscription_schedules' => 'plan_subscription_schedules',
        'plan_subscription_usage' => 'plan_subscription_usage',
    ],

    // Models
    'models' => [
        'plan' => \Bpuig\Subby\Models\Plan::class,
        'plan_feature' => \Bpuig\Subby\Models\PlanFeature::class,
        'plan_subscription' => \Bpuig\Subby\Models\PlanSubscription::class,
        'plan_subscription_feature' => \Bpuig\Subby\Models\PlanSubscriptionFeature::class,
        'plan_subscription_schedule' => \Bpuig\Subby\Models\PlanSubscriptionSchedule::class,
        'plan_subscription_usage' => \Bpuig\Subby\Models\PlanSubscriptionUsage::class,
    ],

    'services' => [
        'schedule' => [
            'default' => \Bpuig\Subby\Services\ScheduleService::class
        ]
    ]
];
