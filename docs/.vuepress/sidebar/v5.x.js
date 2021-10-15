var sidebar = [
    {
        title: 'The package',   // required
        sidebarDepth: 1,
        collapsable: false,
        children: [
            ['', 'Introduction'],
            ['install/', 'Installation'],
            ['install/migrate-v5.md', 'Upgrade v4 to v5']
        ]
    },
    {
        title: 'Usage',   // required
        sidebarDepth: 2,
        collapsable: false,
        children: [
            ['models/', 'Models'],
            ['models/plan-model.md', 'Plan Model'],
            ['models/plan-feature-model.md', 'Plan Feature Model'],
            ['models/plan-subscription-model.md', 'Plan Subscription Model'],
            ['models/plan-subscription-feature-model.md', 'Plan Subscription Feature Model'],
            ['models/plan-subscription-schedule-model.md', 'Plan Subscription Schedule Model'],
        ]
    }
]

module.exports = {sidebar}
