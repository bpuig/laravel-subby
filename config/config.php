<?php

declare(strict_types=1);

return [
    // Database Tables
    'tables' => [
        'plans' => 'plans',
        'plan_features' => 'plan_features',
        'plan_subscriptions' => 'plan_subscriptions',
        'plan_subscription_features' => 'plan_subscription_features',
        'plan_subscription_usage' => 'plan_subscription_usage',
    ],

    // Models
    'models' => [
        'plan' => \Bpuig\Subby\Models\Plan::class,
        'plan_feature' => \Bpuig\Subby\Models\PlanFeature::class,
        'plan_subscription' => \Bpuig\Subby\Models\PlanSubscription::class,
        'plan_subscription_feature' => \Bpuig\Subby\Models\PlanSubscriptionFeature::class,
        'plan_subscription_usage' => \Bpuig\Subby\Models\PlanSubscriptionUsage::class,
    ],

    // Plan schedule settings (Optional if you do not use IsScheduled trait)
    'schedule' => [
        'schedules_per_subscription' => null, // Maximum number of schedules allowed for a subscription (null for no limit)
        'tables' => [
            'plan_subscription_schedules' => 'plan_subscription_schedules'
        ],
        'models' => [
            'plan_subscription_schedule' => \Bpuig\Subby\Models\PlanSubscriptionSchedule::class,
        ],
        'services' => [
            'default' => \Bpuig\Subby\Services\PlanSubscriptionSchedule\DefaultScheduleService::class
        ]
    ]
];
