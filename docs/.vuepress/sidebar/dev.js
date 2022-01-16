var sidebar = [
    {
        title: 'The package',
        sidebarDepth: 1,
        collapsable: false,
        children: [
            ['', 'Introduction'],
            ['install/', 'Installation'],
            ['install/migrate-dev.md', 'Upgrade v5.x to dev']
        ]
    },
    {
        title: 'Models',
        sidebarDepth: 2,
        collapsable: false,
        children: [
            ['models/', 'Models'],
            ['models/plan-model.md', 'Plan Model'],
            ['models/plan-feature-model.md', 'Plan Feature Model'],
            ['models/plan-subscription-model.md', 'Plan Subscription Model'],
            ['models/plan-subscription-feature-model.md', 'Plan Subscription Feature Model'],
            ['models/plan-subscription-schedule-model.md', 'Plan Subscription Schedule Model']
        ]
    },
    {
        title: 'Payments',
        sidebarDepth: 2,
        collapsable: false,
        children: [
            {
                title: 'Jobs',
                sidebarDepth: 0,
                collapsable: false,
                children: [
                    ['payments/subscription-payment-queuer-job.md', 'Subscription Payment Queuer'],
                    ['payments/subscription-renewal-payment-job.md', 'Subscription Payment'],
                    ['payments/subscription-schedule-payment-job.md', 'Subscription Schedule Payment']
                ]
            },

        ]
    }
]

module.exports = { sidebar }
